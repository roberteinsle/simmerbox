<x-print-layout>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Einkaufsliste - {{ $groceryList->name }}</h1>
    @if($groceryList->week_start && $groceryList->week_end)
        <p class="text-sm text-gray-500 mb-6">{{ $groceryList->week_start->format('d.m.Y') }} - {{ $groceryList->week_end->format('d.m.Y') }}</p>
    @endif

    @php
        $unchecked = $groceryList->groceryItems->where('is_checked', false);
        $checked = $groceryList->groceryItems->where('is_checked', true);
    @endphp

    @if($unchecked->count() > 0)
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Noch zu besorgen</h2>
            <ul class="space-y-1">
                @foreach($unchecked as $item)
                    <li class="flex items-center gap-2 text-sm">
                        <span class="w-4 h-4 border border-gray-400 rounded-sm flex-shrink-0"></span>
                        <span>
                            @if($item->amount)
                                <strong>{{ rtrim(rtrim(number_format($item->amount, 2, ',', '.'), '0'), ',') }}{{ $item->unit ? ' ' . $item->unit : '' }}</strong>
                            @endif
                            {{ $item->name }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($checked->count() > 0)
        <div>
            <h2 class="text-lg font-semibold text-gray-400 mb-3">Erledigt</h2>
            <ul class="space-y-1">
                @foreach($checked as $item)
                    <li class="flex items-center gap-2 text-sm text-gray-400 line-through">
                        <span class="w-4 h-4 border border-gray-300 rounded-sm flex-shrink-0 bg-gray-200 flex items-center justify-center">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </span>
                        <span>
                            @if($item->amount)
                                {{ rtrim(rtrim(number_format($item->amount, 2, ',', '.'), '0'), ',') }}{{ $item->unit ? ' ' . $item->unit : '' }}
                            @endif
                            {{ $item->name }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($groceryList->groceryItems->count() === 0)
        <p class="text-gray-400 text-center py-8">Keine Eintraege auf der Einkaufsliste.</p>
    @endif

    <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-400 text-center">
        Erstellt mit Simmerbox | {{ now()->format('d.m.Y H:i') }}
    </div>
</x-print-layout>
