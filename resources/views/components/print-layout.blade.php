<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Simmerbox') }} - Drucken</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {
                nav, .no-print, button { display: none !important; }
                body { background: white !important; color: black !important; }
                .print-container { max-width: 100% !important; padding: 0 !important; }
                .page-break { page-break-before: always; }
                .avoid-break { page-break-inside: avoid; }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="print-container max-w-4xl mx-auto p-6">
            <div class="no-print mb-4 flex justify-between items-center">
                <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-800">&larr; Zurueck</a>
                <button onclick="window.print()" class="bg-olive-500 text-white px-4 py-2 rounded hover:bg-olive-600">
                    Drucken
                </button>
            </div>

            {{ $slot }}
        </div>
    </body>
</html>
