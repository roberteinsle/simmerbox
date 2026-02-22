<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Wochenplan
            </h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('groceries.generate', ['week' => $monday->toDateString()]) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
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
                    {{ $monday->format('d.m.') }} - {{ $sunday->format('d.m.Y') }}
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
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden {{ $day['isToday'] ? 'ring-2 ring-olive-500' : '' }}">
                        <!-- Day Header -->
                        <div class="px-3 py-2 {{ $day['isToday'] ? 'bg-olive-500 text-white' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            <div class="font-semibold text-sm">{{ $day['name'] }}</div>
                            <div class="text-xs {{ $day['isToday'] ? 'text-olive-100' : 'text-gray-500 dark:text-gray-400' }}">{{ $day['date']->format('d.m.') }}</div>
                        </div>

                        <!-- Content -->
                        <div class="p-3 min-h-[120px] flex flex-col">
                            @if($mealPlan && $mealPlan->recipe_id)
                                <a href="{{ route('recipes.show', $mealPlan->recipe) }}" class="flex-1 group">
                                    @if($mealPlan->recipe->image_path)
                                        <img src="{{ asset('storage/' . $mealPlan->recipe->image_path) }}" alt="{{ $mealPlan->recipe->title }}" class="w-full h-24 object-cover rounded-md mb-2">
                                    @endif
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-olive-500 transition">
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
                            @elseif($mealPlan && $mealPlan->note)
                                <div class="flex-1">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $mealPlan->note }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1 italic">Freitext</div>
                                </div>
                                <form method="POST" action="{{ route('meal-plan.destroy', $mealPlan) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                        Entfernen
                                    </button>
                                </form>
                            @else
                                <!-- Recipe / Note Selector -->
                                <div x-data="{ mode: null }" class="flex-1 flex flex-col justify-center">
                                    <div x-show="!mode" class="flex gap-2 justify-center py-2">
                                        <button @click="mode = 'recipe'" class="flex flex-col items-center py-2 px-3 text-gray-400 dark:text-gray-500 hover:text-olive-500 dark:hover:text-olive-400 transition">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                            <span class="text-xs mt-1">Rezept</span>
                                        </button>
                                        <button @click="mode = 'note'" class="flex flex-col items-center py-2 px-3 text-gray-400 dark:text-gray-500 hover:text-olive-500 dark:hover:text-olive-400 transition">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            <span class="text-xs mt-1">Freitext</span>
                                        </button>
                                    </div>

                                    <!-- Recipe Selector -->
                                    <div x-show="mode === 'recipe'" x-cloak class="mt-2">
                                        <form method="POST" action="{{ route('meal-plan.store') }}">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $dateStr }}">
                                            <select name="recipe_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                                                <option value="">Rezept waehlen...</option>
                                                @if($hasBioKiste)
                                                    <optgroup label="Bio-Kiste">
                                                        @foreach($bioKisteRecipes as $recipe)
                                                            <option value="{{ $recipe->id }}">{{ $recipe->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if($fiveStarRecipes->isNotEmpty())
                                                    <optgroup label="Favoriten">
                                                        @foreach($fiveStarRecipes as $recipe)
                                                            <option value="{{ $recipe->id }}">{{ $recipe->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                <optgroup label="Weitere Rezepte">
                                                    @foreach($remainingRecipes as $recipe)
                                                        <option value="{{ $recipe->id }}">{{ $recipe->title }}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </form>
                                        <div class="mt-1">
                                            <button @click="mode = null" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">Abbrechen</button>
                                        </div>
                                    </div>

                                    <!-- Note Input -->
                                    <div x-show="mode === 'note'" x-cloak class="mt-2">
                                        <form method="POST" action="{{ route('meal-plan.store') }}">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $dateStr }}">
                                            <input type="text" name="note" placeholder="z.B. Pizza bestellen" maxlength="500" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500 mb-1">
                                            <div class="flex justify-between">
                                                <button @click.prevent="mode = null" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">Abbrechen</button>
                                                <button type="submit" class="text-xs text-olive-600 hover:text-olive-700 dark:text-olive-400 dark:hover:text-olive-300 font-medium">Speichern</button>
                                            </div>
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
