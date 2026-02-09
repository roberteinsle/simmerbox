<?php

namespace App\Services;

use App\Enums\FrequencyType;
use App\Models\RecurringGrocery;
use Carbon\Carbon;

class RecurrenceSchedulerService
{
    /**
     * Berechnet das naechste Vorkommensdatum basierend auf der Frequenz.
     */
    public function calculateNextOccurrence(RecurringGrocery $item, ?Carbon $after = null): Carbon
    {
        $after = $after ?? Carbon::today();
        $type = FrequencyType::from($item->frequency_type);

        return match ($type) {
            FrequencyType::Daily => $this->nextDaily($after),
            FrequencyType::Weekly => $this->nextWeekly($after, $item->frequency_day),
            FrequencyType::Monthly => $this->nextMonthly($after),
            FrequencyType::MonthlyOnDay => $this->nextMonthlyOnDay($after, $item->frequency_day),
            FrequencyType::EveryXWeeks => $this->nextEveryXWeeks($after, $item->frequency_interval, $item->frequency_day),
        };
    }

    private function nextDaily(Carbon $after): Carbon
    {
        return $after->copy()->addDay();
    }

    private function nextWeekly(Carbon $after, ?int $dayOfWeek): Carbon
    {
        if ($dayOfWeek === null) {
            return $after->copy()->addWeek();
        }

        $next = $after->copy()->next($dayOfWeek);

        // Wenn der naechste Tag heute ist, eine Woche weiter
        if ($next->isSameDay($after)) {
            $next->addWeek();
        }

        return $next;
    }

    private function nextMonthly(Carbon $after): Carbon
    {
        return $after->copy()->addMonth()->startOfMonth();
    }

    private function nextMonthlyOnDay(Carbon $after, ?int $dayOfMonth): Carbon
    {
        $dayOfMonth = $dayOfMonth ?? 1;

        $candidate = $after->copy()->day(min($dayOfMonth, $after->daysInMonth));

        if ($candidate->lte($after)) {
            $candidate->addMonth();
            $candidate->day(min($dayOfMonth, $candidate->daysInMonth));
        }

        return $candidate;
    }

    private function nextEveryXWeeks(Carbon $after, int $interval, ?int $dayOfWeek): Carbon
    {
        $interval = max(1, $interval);

        if ($dayOfWeek === null) {
            return $after->copy()->addWeeks($interval);
        }

        $next = $after->copy()->next($dayOfWeek);

        if ($next->isSameDay($after)) {
            $next->addWeeks($interval);
        } else {
            // Erste Occurrence ist der naechste passende Tag,
            // danach im X-Wochen-Rhythmus
            $next->addWeeks($interval - 1);
        }

        return $next;
    }

    /**
     * Aktualisiert alle faelligen recurring items:
     * Setzt next_occurrence auf das naechste Datum nach heute.
     */
    public function updateDueItems(): int
    {
        $today = Carbon::today();
        $count = 0;

        RecurringGrocery::where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('next_occurrence')
                    ->orWhere('next_occurrence', '<=', $today->toDateString());
            })
            ->each(function (RecurringGrocery $item) use ($today, &$count) {
                $next = $this->calculateNextOccurrence($item, $today);
                $item->update(['next_occurrence' => $next->toDateString()]);
                $count++;
            });

        return $count;
    }

    /**
     * Gibt alle faelligen Items fuer einen Haushalt in einem Zeitraum zurueck.
     */
    public function getDueItems(int $householdId, Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        return RecurringGrocery::where('household_id', $householdId)
            ->where('is_active', true)
            ->where('next_occurrence', '>=', $start->toDateString())
            ->where('next_occurrence', '<=', $end->toDateString())
            ->get();
    }
}
