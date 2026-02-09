<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Wiederkehrende Einkaeufe
            </h2>
            <a href="{{ route('groceries.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Zur Einkaufsliste
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Neuer Eintrag --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Neuen Eintrag hinzufuegen</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Format: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Name, Menge, Wiederholung</code>
                </p>

                <form method="POST" action="{{ route('recurring-groceries.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <input type="text" name="raw_input" value="{{ old('raw_input') }}" placeholder="z.B. Milch, 1L, woechentlich am Montag" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                        @error('raw_input')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-olive-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-olive-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Hinzufuegen
                    </button>
                </form>

                {{-- Beispiele --}}
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-2">Beispiele:</p>
                    <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <li><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Milch, 1L, woechentlich am Montag</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Brot, 1 Stueck, taeglich</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Eier, 10 Stueck, alle 2 Wochen am Freitag</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Mehl, 1kg, monatlich am 15.</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">Kaffee, 500g, monatlich</code></li>
                    </ul>
                </div>
            </div>

            {{-- Liste --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $items->count() }} {{ $items->count() === 1 ? 'Eintrag' : 'Eintraege' }}
                    </h3>
                </div>

                @if($items->count() > 0)
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($items as $item)
                            <li class="px-4 py-3 flex items-center gap-4 {{ !$item->is_active ? 'opacity-50' : '' }}">
                                {{-- Status-Indikator --}}
                                <div class="flex-shrink-0">
                                    <span class="inline-block w-3 h-3 rounded-full {{ $item->is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $item->name }}
                                        @if($item->amount)
                                            <span class="text-gray-500 dark:text-gray-400">
                                                ({{ rtrim(rtrim(number_format($item->amount, 2, ',', '.'), '0'), ',') }}{{ $item->unit ? ' ' . $item->unit : '' }})
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \App\Services\RecurrenceParserService::describe(\App\Enums\FrequencyType::from($item->frequency_type), $item->frequency_day, $item->frequency_interval) }}
                                        @if($item->next_occurrence)
                                            &middot; Naechstes Mal: {{ $item->next_occurrence->format('d.m.Y') }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Aktionen --}}
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    <form method="POST" action="{{ route('recurring-groceries.toggle', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs px-2 py-1 rounded {{ $item->is_active ? 'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400' : 'text-green-600 hover:text-green-800 dark:text-green-400' }}">
                                            {{ $item->is_active ? 'Pausieren' : 'Aktivieren' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('recurring-groceries.destroy', $item) }}" onsubmit="return confirm('Eintrag wirklich loeschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 px-2 py-1">
                                            Loeschen
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <p>Noch keine wiederkehrenden Einkaeufe definiert.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
