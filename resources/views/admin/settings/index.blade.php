<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin - Einstellungen
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Admin Navigation --}}
            @include('admin._nav')

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                @foreach($groups as $groupKey => $group)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            {{ $group['label'] }}
                        </h3>

                        <div class="space-y-4">
                            @foreach($group['settings'] as $settingKey => $config)
                                @php
                                    $fieldName = 'settings_' . str_replace('.', '_', $settingKey);
                                    $currentValue = $values[$settingKey] ?? '';
                                @endphp

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label for="{{ $fieldName }}" class="sm:w-1/3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $config['label'] }}
                                    </label>
                                    <div class="sm:w-2/3">
                                        @if($config['type'] === 'boolean')
                                            <input type="hidden" name="{{ $fieldName }}" value="0">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="{{ $fieldName }}" value="1" {{ $currentValue ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Aktiviert</span>
                                            </label>
                                        @elseif($config['type'] === 'select')
                                            <select name="{{ $fieldName }}" id="{{ $fieldName }}" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                                @foreach($config['options'] as $optValue => $optLabel)
                                                    <option value="{{ $optValue }}" {{ $currentValue == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($config['type'] === 'number')
                                            <input type="number" name="{{ $fieldName }}" id="{{ $fieldName }}" value="{{ $currentValue }}" min="0" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        @else
                                            <input type="{{ $config['type'] }}" name="{{ $fieldName }}" id="{{ $fieldName }}" value="{{ $currentValue }}" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-orange-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-orange-600 transition">
                        Einstellungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
