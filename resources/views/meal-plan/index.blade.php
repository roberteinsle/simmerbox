<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Wochenplan
            </h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('groceries.generate', ['week' => $monday->toDateString()]) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        Einkaufsliste
                    </button>
                </form>
                <a href="{{ route('meal-plan.print', ['week' => $monday->toDateString()]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Drucken
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Week Navigation -->
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('meal-plan.index', ['week' => $prevWeek]) }}" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Vorherige
                </a>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $weekLabel }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400 block sm:inline sm:ml-2">
                        {{ $monday->format('d.m.') }} - {{ $sunday->format('d.m.Y') }}
                    </span>
                </h3>
                <a href="{{ route('meal-plan.index', ['week' => $nextWeek]) }}" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Naechste
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>

            <!-- Week Grid -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                @foreach($days as $day)
                    @php
                        $dateStr = $day['date']->toDateString();
                        $mealPlan = $mealPlans[$dateStr] ?? null;
                    @endphp
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden {{ $day['isToday'] ? 'ring-2 ring-orange-500' : '' }}">
                        <!-- Day Header -->
                        <div class="px-3 py-2 {{ $day['isToday'] ? 'bg-orange-500 text-white' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            <div class="font-semibold text-sm">{{ $day['name'] }}</div>
                            <div class="text-xs {{ $day['isToday'] ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">{{ $day['date']->format('d.m.') }}</div>
                        </div>

                        <!-- Content -->
                        <div class="p-3 min-h-[120px] flex flex-col">
                            @if($mealPlan)
                                <a href="{{ route('recipes.show', $mealPlan->recipe) }}" class="flex-1 group">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-orange-500 transition">
                                        {{ $mealPlan->recipe->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $mealPlan->recipe->category->name }}
                                    </div>
                                </a>
                                <form method="POST" action="{{ route('meal-plan.destroy', $mealPlan) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                        Entfernen
                                    </button>
                                </form>
                            @else
                                <!-- Recipe Selector -->
                                <div x-data="{ open: false, search: '', filtered: [] }" class="flex-1 flex flex-col justify-center">
                                    <button @click="open = !open" class="w-full py-4 text-center text-gray-400 dark:text-gray-500 hover:text-orange-500 dark:hover:text-orange-400 transition">
                                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
                                        <span class="text-xs mt-1 block">Rezept</span>
                                    </button>

                                    <div x-show="open" x-cloak @click.outside="open = false" class="mt-2">
                                        <form method="POST" action="{{ route('meal-plan.store') }}">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $dateStr }}">
                                            <select name="recipe_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                                <option value="">Waehle...</option>
                                                @foreach($recipes as $recipe)
                                                    <option value="{{ $recipe->id }}">{{ $recipe->title }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Realtime-Sync: Bei Aenderungen durch andere Haushaltsmitglieder Seite neu laden
        if (window.Echo) {
            const householdId = @json(auth()->user()->household_id);
            if (householdId) {
                window.Echo.private(`household.${householdId}`)
                    .listen('MealPlanUpdated', (e) => {
                        window.location.reload();
                    });
            }
        }
    </script>
    @endpush
</x-app-layout>
