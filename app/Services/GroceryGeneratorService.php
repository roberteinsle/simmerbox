<?php

namespace App\Services;

use App\Models\GroceryList;
use App\Models\MealPlan;
use App\Models\RecurringGrocery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GroceryGeneratorService
{
    /**
     * Generiert eine Einkaufsliste aus dem Wochenplan.
     * Gleiche Zutaten werden zusammengefasst und Mengen addiert.
     */
    public function generateFromMealPlan(int $householdId, Carbon $monday): GroceryList
    {
        $sunday = $monday->copy()->endOfWeek(Carbon::SUNDAY);

        return DB::transaction(function () use ($householdId, $monday, $sunday) {
            // Vorherige aktive Listen fuer diesen Haushalt deaktivieren
            GroceryList::where('household_id', $householdId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Neue Liste erstellen
            $groceryList = GroceryList::create([
                'household_id' => $householdId,
                'name' => 'KW ' . $monday->isoWeek() . ' / ' . $monday->year,
                'week_start' => $monday->toDateString(),
                'week_end' => $sunday->toDateString(),
                'is_active' => true,
            ]);

            // Alle Meal Plans der Woche mit Zutaten laden
            $mealPlans = MealPlan::where('household_id', $householdId)
                ->whereBetween('date', [$monday->toDateString(), $sunday->toDateString()])
                ->with('recipe.ingredients')
                ->get();

            // Zutaten aggregieren: gleicher Name + gleiche Einheit zusammenfassen
            $aggregated = [];

            foreach ($mealPlans as $mealPlan) {
                if (!$mealPlan->recipe || !$mealPlan->recipe->ingredients) {
                    continue;
                }

                foreach ($mealPlan->recipe->ingredients as $ingredient) {
                    $key = mb_strtolower(trim($ingredient->name)) . '|' . mb_strtolower(trim($ingredient->unit ?? ''));

                    if (isset($aggregated[$key])) {
                        // Menge addieren
                        if ($ingredient->amount) {
                            $aggregated[$key]['amount'] = ($aggregated[$key]['amount'] ?? 0) + $ingredient->amount;
                        }
                        // Rezept-ID hinzufuegen
                        if (!in_array($mealPlan->recipe_id, $aggregated[$key]['source_recipe_ids'])) {
                            $aggregated[$key]['source_recipe_ids'][] = $mealPlan->recipe_id;
                        }
                    } else {
                        $aggregated[$key] = [
                            'name' => trim($ingredient->name),
                            'amount' => $ingredient->amount,
                            'unit' => $ingredient->unit,
                            'source_recipe_ids' => [$mealPlan->recipe_id],
                        ];
                    }
                }
            }

            // Wiederkehrende Einkaeufe hinzufuegen (faellig in dieser Woche)
            $recurringItems = RecurringGrocery::where('household_id', $householdId)
                ->where('is_active', true)
                ->where(function ($q) use ($monday, $sunday) {
                    $q->whereBetween('next_occurrence', [$monday->toDateString(), $sunday->toDateString()])
                        ->orWhereNull('next_occurrence');
                })
                ->get();

            foreach ($recurringItems as $recurring) {
                $key = mb_strtolower(trim($recurring->name)) . '|' . mb_strtolower(trim($recurring->unit ?? ''));

                if (isset($aggregated[$key])) {
                    if ($recurring->amount) {
                        $aggregated[$key]['amount'] = ($aggregated[$key]['amount'] ?? 0) + $recurring->amount;
                    }
                } else {
                    $aggregated[$key] = [
                        'name' => trim($recurring->name),
                        'amount' => $recurring->amount,
                        'unit' => $recurring->unit,
                        'source_recipe_ids' => [],
                    ];
                }
            }

            // Items sortiert erstellen
            $sortOrder = 0;
            foreach ($aggregated as $item) {
                $groceryList->groceryItems()->create([
                    'name' => $item['name'],
                    'amount' => $item['amount'],
                    'unit' => $item['unit'],
                    'is_checked' => false,
                    'is_manual' => false,
                    'source_recipe_ids' => $item['source_recipe_ids'],
                    'sort_order' => $sortOrder++,
                ]);
            }

            return $groceryList;
        });
    }
}
