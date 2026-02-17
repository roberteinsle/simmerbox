<x-print-layout>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $recipe->title }}</h1>

    <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-1">
        <span>{{ $recipe->category->name }}</span>
        @if($recipe->preparation_time)
            <span>{{ $recipe->preparation_time }} Minuten</span>
        @endif
        <span>{{ $recipe->portions }} Portionen</span>
    </div>

    @if($recipe->description)
        <p class="text-sm text-gray-600 mb-4">{{ $recipe->description }}</p>
    @else
        <div class="mb-4"></div>
    @endif

    <h2 class="text-lg font-semibold text-gray-900 mb-2">Zutaten</h2>
    <table class="w-full mb-6 text-sm">
        @foreach($recipe->ingredients as $ingredient)
            <tr class="border-b border-gray-100">
                <td class="py-1 pr-4 text-right w-32 text-gray-600">
                    @if($ingredient->amount)
                        {{ rtrim(rtrim(number_format($ingredient->amount, 2, ',', '.'), '0'), ',') }}
                        {{ $ingredient->unit }}
                    @endif
                </td>
                <td class="py-1 text-gray-800">
                    {{ $ingredient->name }}
                    @if($ingredient->note)
                        <span class="text-gray-400">({{ $ingredient->note }})</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <h2 class="text-lg font-semibold text-gray-900 mb-2">Zubereitung</h2>
    <ol class="space-y-3">
        @foreach($recipe->preparationSteps as $step)
            <li class="flex gap-3 text-sm">
                <span class="flex-shrink-0 font-semibold text-gray-500 w-6 text-right">{{ $step->step_number }}.</span>
                <span class="text-gray-800">{{ $step->instruction }}</span>
            </li>
        @endforeach
    </ol>

    <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-400 text-center">
        Erstellt mit Simmerbox | {{ now()->format('d.m.Y H:i') }}
    </div>
</x-print-layout>
