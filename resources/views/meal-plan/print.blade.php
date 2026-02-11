<x-print-layout>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Wochenplan - {{ $weekLabel }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $monday->format('d.m.Y') }} - {{ $monday->copy()->addDays(6)->format('d.m.Y') }}</p>

    <div class="space-y-4">
        @foreach($days as $day)
            @php
                $dateStr = $day['date']->toDateString();
                $mealPlan = $mealPlans[$dateStr] ?? null;
            @endphp
            <div class="avoid-break border-b border-gray-200 pb-4 last:border-0">
                <div class="flex items-baseline gap-3 mb-2">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $day['name'] }}</h2>
                    <span class="text-sm text-gray-500">{{ $day['date']->format('d.m.Y') }}</span>
                </div>

                @if($mealPlan)
                    <div class="ml-4">
                        <h3 class="font-medium text-gray-800 text-base">{{ $mealPlan->recipe->title }}</h3>
                        <span class="text-sm text-gray-500">{{ $mealPlan->recipe->category->name }} | {{ $mealPlan->recipe->portions }} Portionen</span>
                    </div>
                @else
                    <p class="ml-4 text-sm text-gray-400 italic">Kein Rezept geplant</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-400 text-center">
        Erstellt mit Simmerbox | {{ now()->format('d.m.Y H:i') }}
    </div>
</x-print-layout>
