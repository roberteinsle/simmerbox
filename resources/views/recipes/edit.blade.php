<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Rezept bearbeiten: {{ $recipe->title }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('recipes.update', $recipe) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('recipes._form', ['recipe' => $recipe, 'categories' => $categories])

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('recipes.show', $recipe) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                            Abbrechen
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                            Aenderungen speichern
                        </button>
                    </div>
                </form>
            </div>

            {{-- Bewertung --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Bewertung</h3>
                <x-star-rating :recipe-id="$recipe->id" :user-rating="$userRating" :average="$averageRating" :count="$ratingCount" />
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
        </div>
    </div>
</x-app-layout>
