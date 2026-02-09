<?php

namespace App\Http\Controllers;

use App\Models\RecurringGrocery;
use App\Services\RecurrenceParserService;
use App\Services\RecurrenceSchedulerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringGroceryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $items = RecurringGrocery::where('household_id', $user->household_id)
            ->orderBy('name')
            ->get();

        return view('recurring-groceries.index', compact('items'));
    }

    public function store(Request $request, RecurrenceParserService $parser, RecurrenceSchedulerService $scheduler): RedirectResponse
    {
        $request->validate([
            'raw_input' => ['required', 'string', 'max:500'],
        ], [
            'raw_input.required' => 'Bitte gib einen Eintrag ein.',
        ]);

        $parsed = $parser->parse($request->input('raw_input'));

        if (!$parsed) {
            return back()
                ->withInput()
                ->with('error', 'Eingabe konnte nicht erkannt werden. Bitte verwende das Format: "Name, Menge, Wiederholung" (z.B. "Milch, 1L, woechentlich am Montag")');
        }

        $user = auth()->user();

        $item = RecurringGrocery::create([
            'household_id' => $user->household_id,
            'name' => $parsed['name'],
            'amount' => $parsed['amount'],
            'unit' => $parsed['unit'],
            'frequency_type' => $parsed['frequency_type']->value,
            'frequency_day' => $parsed['frequency_day'],
            'frequency_interval' => $parsed['frequency_interval'],
            'raw_input' => $parsed['raw_input'],
            'is_active' => true,
        ]);

        // Naechstes Vorkommen berechnen
        $next = $scheduler->calculateNextOccurrence($item);
        $item->update(['next_occurrence' => $next->toDateString()]);

        return back()->with('success', 'Wiederkehrender Eintrag erstellt: ' . $parsed['name']);
    }

    public function toggleActive(RecurringGrocery $recurringGrocery): RedirectResponse
    {
        $user = auth()->user();

        if ($recurringGrocery->household_id !== $user->household_id) {
            abort(403);
        }

        $recurringGrocery->update(['is_active' => !$recurringGrocery->is_active]);

        $status = $recurringGrocery->is_active ? 'aktiviert' : 'deaktiviert';

        return back()->with('success', "Eintrag {$status}.");
    }

    public function destroy(RecurringGrocery $recurringGrocery): RedirectResponse
    {
        $user = auth()->user();

        if ($recurringGrocery->household_id !== $user->household_id) {
            abort(403);
        }

        $recurringGrocery->delete();

        return back()->with('success', 'Eintrag geloescht.');
    }
}
