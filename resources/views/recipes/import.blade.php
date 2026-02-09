<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Rezept per JSON importieren
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('recipes.import.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="json" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">JSON einfuegen</label>
                        <textarea name="json" id="json" rows="18" placeholder='{{ '{
  "title": "Spaghetti Bolognese",
  "description": "Klassiker der italienischen Kueche",
  "category": "Hauptgericht",
  "portions": 4,
  "preparation_time": 45,
  "tags": ["italienisch", "pasta"],
  "ingredients": [
    {"name": "Spaghetti", "amount": 500, "unit": "g"},
    {"name": "Hackfleisch", "amount": 400, "unit": "g"}
  ],
  "steps": [
    "Wasser zum Kochen bringen.",
    "Hackfleisch anbraten."
  ]
}' }}' class="w-full font-mono text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">{{ old('json') }}</textarea>
                        @error('json')
                            @if(is_array($errors->get('json')))
                                @foreach($errors->get('json') as $error)
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                                @endforeach
                            @else
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <!-- JSON Format Help -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Erwartetes Format</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li><strong>title</strong> (erforderlich) - Name des Rezepts</li>
                            <li><strong>description</strong> (optional) - Kurze Beschreibung</li>
                            <li><strong>category</strong> (optional) - z.B. "Hauptgericht", "Dessert"</li>
                            <li><strong>portions</strong> (optional, Standard: 4) - Anzahl Portionen</li>
                            <li><strong>preparation_time</strong> (optional) - Zubereitungszeit in Minuten</li>
                            <li><strong>tags</strong> (optional) - Array von Tags</li>
                            <li><strong>ingredients</strong> (erforderlich) - Array mit {name, amount, unit}</li>
                            <li><strong>steps</strong> (erforderlich) - Array von Zubereitungsschritten (Strings)</li>
                        </ul>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                            Abbrechen
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                            Importieren
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
