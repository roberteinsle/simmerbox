<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin - Kategorien
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin._nav')

            {{-- Neue Kategorie --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
                <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Neue Kategorie..." required class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md text-xs text-white font-semibold hover:bg-olive-600 transition">
                        Hinzufuegen
                    </button>
                </form>
                @error('name')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategorie-Liste --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($categories as $category)
                        <li class="px-4 py-3 flex items-center gap-4">
                            <div class="flex-1" x-data="{ editing: false }">
                                <div x-show="!editing" class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $category->recipes_count }} Rezepte)</span>
                                    <button @click="editing = true" class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400">Bearbeiten</button>
                                </div>
                                <form x-show="editing" x-cloak method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-800 dark:text-green-400">Speichern</button>
                                    <button type="button" @click="editing = false" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Abbrechen</button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Kategorie wirklich loeschen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                    Loeschen
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
