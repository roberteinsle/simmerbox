<?php

namespace App\Http\Controllers;

use App\Events\GroceryItemToggled;
use App\Events\GroceryListUpdated;
use App\Models\GroceryItem;
use App\Models\GroceryList;
use App\Models\Recipe;
use App\Services\GroceryGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroceryListController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $householdId = $user->household_id;

        // Aktive Liste laden oder letzte anzeigen
        if ($request->filled('list')) {
            $groceryList = GroceryList::where('household_id', $householdId)
                ->where('id', $request->input('list'))
                ->with(['groceryItems' => fn ($q) => $q->orderBy('is_checked')->orderBy('sort_order')])
                ->firstOrFail();
        } else {
            $groceryList = GroceryList::where('household_id', $householdId)
                ->where('is_active', true)
                ->with(['groceryItems' => fn ($q) => $q->orderBy('is_checked')->orderBy('sort_order')])
                ->first();
        }

        // Alle Listen des Haushalts (fuer Auswahl)
        $allLists = GroceryList::where('household_id', $householdId)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'is_active', 'created_at']);

        // Statistiken
        $totalItems = $groceryList?->groceryItems->count() ?? 0;
        $checkedItems = $groceryList?->groceryItems->where('is_checked', true)->count() ?? 0;

        return view('groceries.index', compact('groceryList', 'allLists', 'totalItems', 'checkedItems'));
    }

    public function generate(Request $request, GroceryGeneratorService $generator): RedirectResponse
    {
        $this->authorize('create', GroceryList::class);

        $user = auth()->user();

        // Woche bestimmen (aus Wochenplan-Kontext oder aktuelle Woche)
        if ($request->filled('week')) {
            try {
                $monday = Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY);
            } catch (\Exception $e) {
                $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
            }
        } else {
            $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }

        $groceryList = $generator->generateFromMealPlan($user->household_id, $monday);

        $itemCount = $groceryList->groceryItems()->count();

        if ($itemCount === 0) {
            return redirect()->route('groceries.index', ['list' => $groceryList->id])
                ->with('info', 'Keine Rezepte im Wochenplan fuer diese Woche. Du kannst manuell Eintraege hinzufuegen.');
        }

        return redirect()->route('groceries.index', ['list' => $groceryList->id])
            ->with('success', "Einkaufsliste mit {$itemCount} Eintraegen erstellt.");
    }

    public function addItem(Request $request): RedirectResponse
    {
        $request->validate([
            'grocery_list_id' => ['required', 'exists:grocery_lists,id'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Bitte gib einen Namen ein.',
        ]);

        $groceryList = GroceryList::findOrFail($request->input('grocery_list_id'));
        $this->authorize('modify', $groceryList);

        $user = auth()->user();

        $maxSort = $groceryList->groceryItems()->max('sort_order') ?? -1;

        $groceryList->groceryItems()->create([
            'name' => $request->input('name'),
            'amount' => $request->input('amount'),
            'unit' => $request->input('unit'),
            'is_checked' => false,
            'is_manual' => true,
            'sort_order' => $maxSort + 1,
        ]);

        broadcast(new GroceryListUpdated($user->household_id, 'item_added'))->toOthers();

        return back()->with('success', 'Eintrag hinzugefuegt.');
    }

    public function toggleItem(GroceryItem $groceryItem): JsonResponse
    {
        $this->authorize('modify', $groceryItem->groceryList);

        $user = auth()->user();
        $groceryItem->update(['is_checked' => !$groceryItem->is_checked]);

        broadcast(new GroceryItemToggled(
            $groceryItem->id,
            $groceryItem->is_checked,
            $user->household_id,
        ))->toOthers();

        return response()->json([
            'is_checked' => $groceryItem->is_checked,
        ]);
    }

    public function removeItem(GroceryItem $groceryItem): RedirectResponse
    {
        $this->authorize('modify', $groceryItem->groceryList);

        $user = auth()->user();
        $householdId = $groceryItem->groceryList->household_id;
        $groceryItem->delete();

        broadcast(new GroceryListUpdated($householdId, 'item_removed'))->toOthers();

        return back()->with('success', 'Eintrag entfernt.');
    }

    public function destroy(GroceryList $groceryList): RedirectResponse
    {
        $this->authorize('delete', $groceryList);

        $groceryList->delete();

        return redirect()->route('groceries.index')
            ->with('success', 'Einkaufsliste geloescht.');
    }

    public function print(GroceryList $groceryList): View
    {
        $this->authorize('view', $groceryList);

        $groceryList->load(['groceryItems' => fn ($q) => $q->orderBy('is_checked')->orderBy('sort_order')]);

        return view('groceries.print', compact('groceryList'));
    }

    public function addFromRecipe(Request $request, Recipe $recipe): RedirectResponse
    {
        $user = auth()->user();
        $householdId = $user->household_id;

        // Aktive Liste holen oder neue erstellen
        $groceryList = GroceryList::where('household_id', $householdId)
            ->where('is_active', true)
            ->first();

        if (!$groceryList) {
            $groceryList = GroceryList::create([
                'household_id' => $householdId,
                'name' => 'Einkaufsliste',
                'is_active' => true,
            ]);
        }

        $this->authorize('modify', $groceryList);

        $recipe->load('ingredients');

        // Filter: nur "einkauf"-Zutaten oder alle (wenn source nicht gesetzt)
        $sourceFilter = $request->input('source', 'einkauf');
        $ingredients = $recipe->ingredients;

        if ($sourceFilter === 'einkauf') {
            $ingredients = $ingredients->filter(fn ($i) => $i->source === 'einkauf' || $i->source === null);
        }
        // 'alle' = keine Filterung

        $maxSort = $groceryList->groceryItems()->max('sort_order') ?? -1;
        $added = 0;

        foreach ($ingredients as $ingredient) {
            $groceryList->groceryItems()->create([
                'name' => $ingredient->name,
                'amount' => $ingredient->amount,
                'unit' => $ingredient->unit,
                'is_checked' => false,
                'is_manual' => false,
                'source_recipe_ids' => [$recipe->id],
                'sort_order' => ++$maxSort,
            ]);
            $added++;
        }

        broadcast(new GroceryListUpdated($householdId, 'items_added'))->toOthers();

        if ($added === 0) {
            return back()->with('info', 'Keine passenden Zutaten zum Hinzufuegen gefunden.');
        }

        return back()->with('success', "{$added} Zutaten zur Einkaufsliste hinzugefuegt.");
    }
}
