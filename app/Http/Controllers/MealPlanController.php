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
    private const DAY_NAMES = [
        Carbon::SUNDAY => 'Sonntag',
        Carbon::MONDAY => 'Montag',
        Carbon::TUESDAY => 'Dienstag',
        Carbon::WEDNESDAY => 'Mittwoch',
        Carbon::THURSDAY => 'Donnerstag',
        Carbon::FRIDAY => 'Freitag',
        Carbon::SATURDAY => 'Samstag',
    ];

    public function index(Request $request): View
    {
        $user = auth()->user();
        $householdId = $user->household_id;
        $weekStartDay = $user->bio_box_day ?? Carbon::MONDAY;

        $startDate = $this->resolveWeekStart($request->input('week'), $weekStartDay);
        $endDate = $startDate->copy()->addDays(6);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'name' => self::DAY_NAMES[$date->dayOfWeek],
                'isToday' => $date->isToday(),
            ];
        }

        $mealPlans = MealPlan::where('household_id', $householdId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('recipe.category')
            ->get()
            ->keyBy(fn ($mp) => $mp->date->toDateString());

        $recipes = Recipe::orderBy('title')->get(['id', 'title', 'category_id']);

        $prevWeek = $startDate->copy()->subWeek()->toDateString();
        $nextWeek = $startDate->copy()->addWeek()->toDateString();
        $weekLabel = 'KW ' . $startDate->isoWeek() . ' / ' . $startDate->year;

        $monday = $startDate;
        $sunday = $endDate;

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
            'recipe_id' => ['nullable', 'exists:recipes,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $request->input('recipe_id') && ! $request->input('note')) {
            return back()->withErrors(['recipe_id' => 'Bitte ein Rezept oder einen Freitext angeben.']);
        }

        $user = auth()->user();

        MealPlan::updateOrCreate(
            [
                'household_id' => $user->household_id,
                'date' => $request->input('date'),
            ],
            [
                'recipe_id' => $request->input('recipe_id') ?: null,
                'note' => $request->input('recipe_id') ? null : $request->input('note'),
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
        $weekStartDay = $user->bio_box_day ?? Carbon::MONDAY;

        $startDate = $this->resolveWeekStart($request->input('week'), $weekStartDay);
        $endDate = $startDate->copy()->addDays(6);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'name' => self::DAY_NAMES[$date->dayOfWeek],
            ];
        }

        $mealPlans = MealPlan::where('household_id', $householdId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['recipe.ingredients' => fn ($q) => $q->orderBy('sort_order'), 'recipe.category'])
            ->get()
            ->keyBy(fn ($mp) => $mp->date->toDateString());

        $weekLabel = 'KW ' . $startDate->isoWeek() . ' / ' . $startDate->year;

        $monday = $startDate;

        return view('meal-plan.print', compact('days', 'mealPlans', 'weekLabel', 'monday'));
    }

    private function resolveWeekStart(?string $weekParam, int $weekStartDay): Carbon
    {
        if ($weekParam) {
            try {
                $date = Carbon::parse($weekParam);
                return $this->previousOrSame($date, $weekStartDay);
            } catch (\Exception $e) {
                // fall through
            }
        }

        return $this->previousOrSame(Carbon::today(), $weekStartDay);
    }

    private function previousOrSame(Carbon $date, int $targetDay): Carbon
    {
        $date = $date->copy();
        while ($date->dayOfWeek !== $targetDay) {
            $date->subDay();
        }
        return $date;
    }
}
