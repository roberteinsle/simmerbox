<?php

namespace App\Http\Controllers;

use App\Events\MealPlanUpdated;
use App\Models\MealPlan;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealPlanController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $householdId = $user->household_id;

        // Parse week parameter or default to current week
        if ($request->filled('week')) {
            try {
                $monday = Carbon::parse($request->week);
                // Ensure it's a Monday
                if ($monday->dayOfWeek !== Carbon::MONDAY) {
                    $monday = $monday->startOfWeek(Carbon::MONDAY);
                }
            } catch (\Exception $e) {
                $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
            }
        } else {
            $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }

        $sunday = $monday->copy()->endOfWeek(Carbon::SUNDAY);

        // Build days array
        $days = [];
        $dayNames = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];

        for ($i = 0; $i < 7; $i++) {
            $date = $monday->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'name' => $dayNames[$i],
                'isToday' => $date->isToday(),
            ];
        }

        // Load meal plans for this week
        $mealPlans = MealPlan::where('household_id', $householdId)
            ->whereBetween('date', [$monday->toDateString(), $sunday->toDateString()])
            ->with('recipe.category')
            ->get()
            ->keyBy(fn ($mp) => $mp->date->toDateString());

        // Load recipes for the selector
        $recipes = Recipe::orderBy('title')->get(['id', 'title', 'category_id']);

        $prevWeek = $monday->copy()->subWeek()->toDateString();
        $nextWeek = $monday->copy()->addWeek()->toDateString();
        $weekLabel = 'KW ' . $monday->isoWeek() . ' / ' . $monday->year;

        return view('meal-plan.index', compact(
            'days', 'mealPlans', 'recipes', 'monday', 'sunday',
            'prevWeek', 'nextWeek', 'weekLabel'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MealPlan::class);

        $request->validate([
            'date' => ['required', 'date'],
            'recipe_id' => ['required', 'exists:recipes,id'],
        ]);

        $user = auth()->user();

        MealPlan::updateOrCreate(
            [
                'household_id' => $user->household_id,
                'date' => $request->input('date'),
            ],
            [
                'recipe_id' => $request->input('recipe_id'),
            ]
        );

        broadcast(new MealPlanUpdated(
            $user->household_id,
            'updated',
            $request->input('date'),
        ))->toOthers();

        return back()->with('success', 'Wochenplan aktualisiert.');
    }

    public function destroy(MealPlan $mealPlan): RedirectResponse
    {
        $this->authorize('delete', $mealPlan);

        $user = auth()->user();
        $date = $mealPlan->date->toDateString();
        $mealPlan->delete();

        broadcast(new MealPlanUpdated(
            $user->household_id,
            'removed',
            $date,
        ))->toOthers();

        return back()->with('success', 'Eintrag entfernt.');
    }

    public function print(Request $request): View
    {
        $user = auth()->user();
        $householdId = $user->household_id;

        if ($request->filled('week')) {
            try {
                $monday = Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY);
            } catch (\Exception $e) {
                $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
            }
        } else {
            $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }

        $sunday = $monday->copy()->endOfWeek(Carbon::SUNDAY);

        $dayNames = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $monday->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'name' => $dayNames[$i],
            ];
        }

        $mealPlans = MealPlan::where('household_id', $householdId)
            ->whereBetween('date', [$monday->toDateString(), $sunday->toDateString()])
            ->with(['recipe.ingredients' => fn ($q) => $q->orderBy('sort_order'), 'recipe.category'])
            ->get()
            ->keyBy(fn ($mp) => $mp->date->toDateString());

        $weekLabel = 'KW ' . $monday->isoWeek() . ' / ' . $monday->year;

        return view('meal-plan.print', compact('days', 'mealPlans', 'weekLabel', 'monday'));
    }
}
