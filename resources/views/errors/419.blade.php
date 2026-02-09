<x-guest-layout>
    <div class="text-center">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">419</h1>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Sitzung abgelaufen</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Deine Sitzung ist abgelaufen. Bitte lade die Seite neu und versuche es erneut.</p>
        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-orange-600 transition">
            Seite neu laden
        </a>
    </div>
</x-guest-layout>
