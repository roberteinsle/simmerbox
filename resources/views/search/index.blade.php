<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Suche
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Suchformular --}}
            <form method="GET" action="{{ route('search') }}" class="mb-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Rezepte suchen..." autofocus class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                    </div>
                    <select name="category" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500 text-sm">
                        <option value="">Alle Kategorien</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="tag" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500 text-sm">
                        <option value="">Alle Tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ $tagId == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Suchen
                    </button>
                </div>
            </form>

            {{-- Ergebnisse --}}
            @if($results !== null)
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $results->total() }} {{ $results->total() === 1 ? 'Ergebnis' : 'Ergebnisse' }} fuer
                        <span class="font-medium text-gray-900 dark:text-gray-100">&bdquo;{{ $query }}&ldquo;</span>
                    </p>
                </div>

                @if($results->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($results as $recipe)
                            <a href="{{ route('recipes.show', $recipe) }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden hover:shadow-md transition group">
                                @if($recipe->image_path)
                                    <div class="aspect-video bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <img src="{{ asset('storage/' . str_replace('.', '_thumb.', $recipe->image_path)) }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-olive-500 transition">{{ $recipe->title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs bg-olive-100 dark:bg-olive-900 text-olive-700 dark:text-olive-300 px-2 py-0.5 rounded-full">{{ $recipe->category->name }}</span>
                                        @if($recipe->preparation_time)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $recipe->preparation_time }} Min.</span>
                                        @endif
                                    </div>
                                    @if($recipe->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach($recipe->tags->take(3) as $tag)
                                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $results->links() }}
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Keine Ergebnisse</h3>
                        <p class="text-gray-500 dark:text-gray-400">Versuche andere Suchbegriffe oder entferne Filter.</p>
                    </div>
                @endif
            @else
                {{-- Initiale Ansicht (kein Suchbegriff) --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Rezepte durchsuchen</h3>
                    <p class="text-gray-500 dark:text-gray-400">Suche nach Rezeptnamen, Beschreibungen oder Zutaten.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
