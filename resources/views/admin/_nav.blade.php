<div class="flex gap-2 mb-6 overflow-x-auto">
    <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.settings*') ? 'bg-olive-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
        Einstellungen
    </a>
    <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.users*') ? 'bg-olive-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
        Benutzer
    </a>
    <a href="{{ route('admin.categories') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.categories*') ? 'bg-olive-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
        Kategorien
    </a>
</div>
