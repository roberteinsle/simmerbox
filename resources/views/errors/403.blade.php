<x-guest-layout>
    <div class="text-center">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">403</h1>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Zugriff verweigert</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $exception->getMessage() ?: 'Du hast keine Berechtigung, diese Seite aufzurufen.' }}</p>
        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-orange-600 transition">
            Zurueck
        </a>
    </div>
</x-guest-layout>
