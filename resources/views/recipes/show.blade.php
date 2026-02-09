<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $recipe->title }}
            </h2>
            <div class="flex gap-2">
                @can('update', $recipe)
                    <a href="{{ route('recipes.edit', $recipe) }}" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 transition">
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
                        <span class="inline-block px-3 py-1 text-sm font-medium bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 rounded-full">{{ $recipe->category->name }}</span>

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
                                    <span class="font-medium min-w-[80px] text-right">
                                        @if($ingredient->amount)
                                            {{ rtrim(rtrim(number_format($ingredient->amount, 2, ',', '.'), '0'), ',') }}
                                            {{ $ingredient->unit }}
                                        @endif
                                    </span>
                                    <span>{{ $ingredient->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Preparation Steps -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Zubereitung</h3>
                        <ol class="space-y-4">
                            @foreach($recipe->preparationSteps as $step)
                                <li class="flex gap-4">
                                    <span class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-semibold text-sm">{{ $step->step_number }}</span>
                                    <p class="text-gray-700 dark:text-gray-300 pt-1">{{ $step->instruction }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('recipes.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">&larr; Zurueck zur Uebersicht</a>
            </div>
        </div>
    </div>
</x-app-layout>
