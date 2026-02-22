<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rezepte
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('recipes.import') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                    JSON Import
                </a>
                <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                    Neues Rezept
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('recipes.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="q" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Suche</label>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="Rezept suchen..." class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorie</label>
                        <select name="category" id="category" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                            <option value="">Alle Kategorien</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tag" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tag</label>
                        <select name="tag" id="tag" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                            <option value="">Alle Tags</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sortierung</label>
                        <select name="sort" id="sort" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                            <option value="datum" {{ request('sort', 'datum') == 'datum' ? 'selected' : '' }}>Datum</option>
                            <option value="sterne" {{ request('sort') == 'sterne' ? 'selected' : '' }}>Sterne</option>
                            <option value="titel" {{ request('sort') == 'titel' ? 'selected' : '' }}>Titel</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                            Filtern
                        </button>
                        @if(request()->hasAny(['q', 'category', 'tag', 'sort']))
                            <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                                Zuruecksetzen
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Recipe Grid -->
            @if($recipes->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($recipes as $recipe)
                        <a href="{{ route('recipes.show', $recipe) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition group">
                            <div class="aspect-[4/3] bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                @if($recipe->image_path)
                                    <img src="{{ asset('storage/' . dirname($recipe->image_path) . '/thumbs/' . basename($recipe->image_path)) }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium bg-olive-100 dark:bg-olive-900 text-olive-700 dark:text-olive-300 rounded">{{ $recipe->category->name }}</span>
                                    @foreach($recipe->tags as $tag)
                                        <span class="inline-block px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded hover:bg-olive-100 dark:hover:bg-olive-900 hover:text-olive-700 dark:hover:text-olive-300 transition tag-link" data-tag-id="{{ $tag->id }}">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $recipe->title }}</h3>
                                @if($recipe->ratings_avg_rating)
                                    <div class="flex items-center gap-0.5 mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="text-xs {{ $i <= round($recipe->ratings_avg_rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">&#9733;</span>
                                        @endfor
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">{{ number_format($recipe->ratings_avg_rating, 1, ',', '') }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-3 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    @if($recipe->preparation_time)
                                        <span>{{ $recipe->preparation_time }} Min.</span>
                                    @endif
                                    <span>{{ $recipe->portions }} Portionen</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $recipes->withQueryString()->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">Keine Rezepte gefunden.</p>
                    <a href="{{ route('recipes.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                        Erstes Rezept erstellen
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('.tag-link').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const tagId = this.dataset.tagId;
                const url = new URL('{{ route('recipes.index') }}');
                url.searchParams.set('tag', tagId);
                window.location.href = url.toString();
            });
        });
    </script>
</x-app-layout>
