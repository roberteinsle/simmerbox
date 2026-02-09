<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Einkaufsliste
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('recurring-groceries.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Wiederkehrend
                </a>
                @if($groceryList)
                    <a href="{{ route('groceries.print', $groceryList) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Drucken
                    </a>
                @endif
                <form method="POST" action="{{ route('groceries.generate') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Aus Wochenplan generieren
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Listen-Auswahl --}}
            @if($allLists->count() > 1)
                <div class="mb-4">
                    <label for="list-select" class="sr-only">Liste waehlen</label>
                    <select id="list-select" onchange="window.location.href='{{ route('groceries.index') }}?list=' + this.value" class="w-full sm:w-auto border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        @foreach($allLists as $list)
                            <option value="{{ $list->id }}" {{ $groceryList && $groceryList->id === $list->id ? 'selected' : '' }}>
                                {{ $list->name }} {{ $list->is_active ? '(aktiv)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($groceryList)
                {{-- Fortschrittsbalken --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $groceryList->name }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $checkedItems }} / {{ $totalItems }} erledigt
                        </span>
                    </div>
                    @if($totalItems > 0)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full transition-all duration-300" style="width: {{ ($checkedItems / $totalItems) * 100 }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Einkaufsliste --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden" x-data="groceryList()">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($groceryList->groceryItems as $item)
                            <li class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $item->is_checked ? 'bg-gray-50 dark:bg-gray-700/30' : '' }}" x-data="{ checked: {{ $item->is_checked ? 'true' : 'false' }} }">
                                {{-- Checkbox --}}
                                <button
                                    @click="toggleItem({{ $item->id }}, $el); checked = !checked"
                                    class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition"
                                    :class="checked ? 'bg-orange-500 border-orange-500' : 'border-gray-300 dark:border-gray-600 hover:border-orange-400'"
                                >
                                    <svg x-show="checked" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>

                                {{-- Name + Menge --}}
                                <div class="flex-1 min-w-0" :class="checked ? 'opacity-50' : ''">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" :class="checked ? 'line-through' : ''">
                                        @if($item->amount)
                                            <span class="text-gray-500 dark:text-gray-400">{{ rtrim(rtrim(number_format($item->amount, 2, ',', '.'), '0'), ',') }}{{ $item->unit ? ' ' . $item->unit : '' }}</span>
                                        @endif
                                        {{ $item->name }}
                                    </span>
                                    @if($item->is_manual)
                                        <span class="ml-1 text-xs text-orange-500">(manuell)</span>
                                    @endif
                                </div>

                                {{-- Entfernen --}}
                                <form method="POST" action="{{ route('groceries.remove-item', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex-shrink-0 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Keine Eintraege. Generiere eine Liste aus dem Wochenplan oder fuege manuell Eintraege hinzu.
                            </li>
                        @endforelse
                    </ul>

                    {{-- Manuellen Eintrag hinzufuegen --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <form method="POST" action="{{ route('groceries.add-item') }}" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="grocery_list_id" value="{{ $groceryList->id }}">
                            <input type="text" name="name" placeholder="Neuer Eintrag..." required class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <input type="text" name="amount" placeholder="Menge" class="w-20 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <input type="text" name="unit" placeholder="Einheit" class="w-20 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-orange-500 border border-transparent rounded-md text-xs text-white font-semibold hover:bg-orange-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Liste loeschen --}}
                <div class="mt-4 flex justify-end">
                    <form method="POST" action="{{ route('groceries.destroy', $groceryList) }}" onsubmit="return confirm('Einkaufsliste wirklich loeschen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            Liste loeschen
                        </button>
                    </form>
                </div>

            @else
                {{-- Keine Liste vorhanden --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Noch keine Einkaufsliste</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        Generiere eine Einkaufsliste aus deinem Wochenplan, um automatisch alle benoetigten Zutaten zusammenzufassen.
                    </p>
                    <form method="POST" action="{{ route('groceries.generate') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-orange-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-orange-600 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Aus Wochenplan generieren
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function groceryList() {
            return {
                async toggleItem(itemId, el) {
                    try {
                        const response = await fetch(`/groceries/toggle/${itemId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Socket-ID': window.Echo?.socketId() || '',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Toggle fehlgeschlagen');
                        }
                    } catch (error) {
                        console.error('Fehler beim Umschalten:', error);
                        window.location.reload();
                    }
                }
            }
        }

        // Realtime-Sync: Bei Aenderungen durch andere Haushaltsmitglieder Seite neu laden
        if (window.Echo) {
            const householdId = @json(auth()->user()->household_id);
            if (householdId) {
                window.Echo.private(`household.${householdId}`)
                    .listen('GroceryItemToggled', (e) => {
                        // Checkbox-Status im DOM aktualisieren
                        const item = document.querySelector(`[data-grocery-item-id="${e.grocery_item_id}"]`);
                        if (item) {
                            window.location.reload();
                        }
                    })
                    .listen('GroceryListUpdated', (e) => {
                        window.location.reload();
                    });
            }
        }
    </script>
    @endpush
</x-app-layout>
