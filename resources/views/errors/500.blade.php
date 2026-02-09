<x-guest-layout>
    <div class="text-center">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">500</h1>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Serverfehler</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Etwas ist schiefgelaufen. Bitte versuche es spaeter erneut.</p>
        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-orange-600 transition">
            Zur Startseite
        </a>
    </div>
</x-guest-layout>
