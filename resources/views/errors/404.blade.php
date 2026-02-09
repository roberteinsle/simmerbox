<x-guest-layout>
    <div class="text-center">
        <h1 class="text-6xl font-bold text-olive-500 mb-4">404</h1>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Seite nicht gefunden</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Die angeforderte Seite existiert nicht oder wurde verschoben.</p>
        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-olive-600 transition">
            Zur Startseite
        </a>
    </div>
</x-guest-layout>
