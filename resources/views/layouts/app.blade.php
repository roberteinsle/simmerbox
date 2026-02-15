<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Simmerbox') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $type => $color)
                @if (session($type))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.300ms>
                        <div class="bg-{{ $color }}-100 dark:bg-{{ $color }}-900 border border-{{ $color }}-400 dark:border-{{ $color }}-600 text-{{ $color }}-700 dark:text-{{ $color }}-300 px-4 py-3 rounded relative flex justify-between items-center" role="alert">
                            <span>{{ session($type) }}</span>
                            <button @click="show = false" class="ml-4 text-{{ $color }}-500 hover:text-{{ $color }}-700 dark:text-{{ $color }}-400">&times;</button>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <footer class="py-4 text-center text-xs text-gray-400 dark:text-gray-600">
                Simmerbox v{{ config('app.version') }}
            </footer>
        </div>

        @stack('scripts')
    </body>
</html>
