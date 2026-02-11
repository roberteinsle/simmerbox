<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $recipe->title }}
            </h2>
            <div class="flex gap-2">
                @can('update', $recipe)
                    <a href="{{ route('recipes.edit', $recipe) }}" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                        Bearbeiten
                    </a>
                @endcan
                @can('delete', $recipe)
                    <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Rezept wirklich loeschen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                            Loeschen
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Image -->
                @if($recipe->image_path)
                    <div class="aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('storage/' . $recipe->image_path) }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6">
                    <!-- Meta Info -->
                    <div class="flex flex-wrap gap-3 mb-4">
                        <span class="inline-block px-3 py-1 text-sm font-medium bg-olive-100 dark:bg-olive-900 text-olive-700 dark:text-olive-300 rounded-full">{{ $recipe->category->name }}</span>

                        @foreach($recipe->tags as $tag)
                            <span class="inline-block px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">{{ $tag->name }}</span>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-6 text-sm text-gray-500 dark:text-gray-400 mb-6">
                        @if($recipe->preparation_time)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $recipe->preparation_time }} Minuten
                            </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $recipe->portions }} Portionen
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            von {{ $recipe->user->name }}
                        </div>
                    </div>

                    @if($recipe->description)
                        <div class="mb-6">
                            <p class="text-gray-700 dark:text-gray-300">{{ $recipe->description }}</p>
                        </div>
                    @endif

                    <!-- Ingredients -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Zutaten</h3>
                        <ul class="space-y-2">
                            @foreach($recipe->ingredients as $ingredient)
                                <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300 py-1 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    @if($ingredient->source)
                                        <span class="flex-shrink-0 w-6 text-center" title="{{ $ingredient->source === 'bio-kiste' ? 'Bio-Kiste' : ($ingredient->source === 'einkauf' ? 'Einkauf' : 'Vorrat') }}">
                                            @if($ingredient->source === 'bio-kiste')
                                                <span class="text-base">🥬</span>
                                            @elseif($ingredient->source === 'einkauf')
                                                <span class="text-base">🛒</span>
                                            @elseif($ingredient->source === 'vorrat')
                                                <span class="text-base">🏠</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="flex-shrink-0 w-6"></span>
                                    @endif
                                    <span class="font-medium min-w-[80px] text-right">
                                        @if($ingredient->amount)
                                            {{ rtrim(rtrim(number_format($ingredient->amount, 2, ',', '.'), '0'), ',') }}
                                            {{ $ingredient->unit }}
                                        @endif
                                    </span>
                                    <span>{{ $ingredient->name }}</span>
                                    @if($ingredient->note)
                                        <span class="text-sm text-gray-400 dark:text-gray-500">({{ $ingredient->note }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($recipe->ingredients->whereNotNull('source')->isNotEmpty())
                            <div class="flex gap-4 mt-3 text-xs text-gray-400 dark:text-gray-500">
                                <span>🥬 Bio-Kiste</span>
                                <span>🛒 Einkauf</span>
                                <span>🏠 Vorrat</span>
                            </div>
                        @endif

                        @if(auth()->user()->household_id)
                            <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <form method="POST" action="{{ route('recipes.add-to-groceries', $recipe) }}">
                                    @csrf
                                    <input type="hidden" name="source" value="einkauf">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-olive-500 text-white rounded-md hover:bg-olive-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>
                                        🛒 Einkauf auf die Liste
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('recipes.add-to-groceries', $recipe) }}">
                                    @csrf
                                    <input type="hidden" name="source" value="alle">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 transition">
                                        Alle Zutaten auf die Liste
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Preparation Steps -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Zubereitung</h3>
                        <ol class="space-y-4">
                            @foreach($recipe->preparationSteps as $step)
                                <li class="flex gap-4">
                                    <span class="flex-shrink-0 w-8 h-8 bg-olive-500 text-white rounded-full flex items-center justify-center font-semibold text-sm">{{ $step->step_number }}</span>
                                    <p class="text-gray-700 dark:text-gray-300 pt-1">{{ $step->instruction }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Wochenplan Schnellzuweisung --}}
            @if(count($freeDays) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        <svg class="w-5 h-5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        In den Wochenplan eintragen
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($freeDays as $day)
                            <form method="POST" action="{{ route('meal-plan.store') }}">
                                @csrf
                                <input type="hidden" name="recipe_id" value="{{ $recipe->id }}">
                                <input type="hidden" name="date" value="{{ $day['date'] }}">
                                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition {{ $day['isToday'] ? 'bg-olive-500 text-white hover:bg-olive-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-olive-100 dark:hover:bg-olive-900 hover:text-olive-700 dark:hover:text-olive-300 border border-gray-200 dark:border-gray-600' }}">
                                    {{ $day['label'] }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Naechste 5 freie Tage</p>
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('recipes.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">&larr; Zurueck zur Uebersicht</a>
            </div>
        </div>
    </div>
</x-app-layout>
