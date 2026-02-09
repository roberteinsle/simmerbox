<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Rezepte Export / Import
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Exportiere oder importiere alle deine Rezepte inkl. Bilder als ZIP-Datei.
        </p>
    </header>

    <div class="mt-6 space-y-6">
        {{-- Export --}}
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Export</h3>
            <a href="{{ route('recipes.export') }}" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Alle Rezepte exportieren (ZIP)
            </a>
        </div>

        {{-- Import --}}
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Import</h3>
            <form method="POST" action="{{ route('recipes.import-zip') }}" enctype="multipart/form-data" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <input type="file" name="zip_file" accept=".zip" class="block w-full text-sm text-gray-500 dark:text-gray-400
                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-olive-50 file:text-olive-700 dark:file:bg-olive-900 dark:file:text-olive-300
                        hover:file:bg-olive-100 dark:hover:file:bg-olive-800">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Importieren
                </button>
            </form>
            @error('zip_file') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>
</section>
