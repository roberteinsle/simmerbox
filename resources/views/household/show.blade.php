<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Haushalt
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($household)
                <!-- Household Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $household->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Eigentuemer: {{ $household->owner->name }}
                            </p>
                        </div>
                        @can('delete', $household)
                            <form method="POST" action="{{ route('household.destroy') }}" onsubmit="return confirm('Haushalt wirklich loeschen? Alle Mitglieder werden entfernt.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    Haushalt loeschen
                                </button>
                            </form>
                        @endcan
                    </div>

                    <!-- Rename (Owner only) -->
                    @can('update', $household)
                        <form method="POST" action="{{ route('household.update') }}" class="mt-4 flex gap-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $household->name }}" class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-olive-500 focus:ring-olive-500">
                            <button type="submit" class="px-3 py-2 bg-olive-500 text-white text-sm rounded-md hover:bg-olive-600 transition">
                                Umbenennen
                            </button>
                        </form>
                    @endcan
                </div>

                <!-- Members -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Mitglieder ({{ $household->users->count() }})</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($household->users as $member)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $member->name }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $member->email }}</span>
                                    @if($member->id === $household->owner_id)
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-olive-100 dark:bg-olive-900 text-olive-700 dark:text-olive-300 rounded-full">Eigentuemer</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Invite -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Mitglieder einladen</h3>

                    <!-- Invite Code -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Einladungscode:</p>
                        <div class="flex gap-2 items-center">
                            <code class="flex-1 px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-sm text-gray-800 dark:text-gray-200 select-all break-all">{{ $household->invite_code }}</code>
                            @can('manageInvites', $household)
                                <form method="POST" action="{{ route('household.regenerate-code') }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        Erneuern
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <!-- Email Invitation -->
                    <form method="POST" action="{{ route('household.invite') }}" class="flex gap-2">
                        @csrf
                        <input type="email" name="email" placeholder="E-Mail-Adresse" required class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-olive-500 focus:ring-olive-500">
                        <button type="submit" class="px-4 py-2 bg-olive-500 text-white text-sm rounded-md hover:bg-olive-600 transition">
                            Einladen
                        </button>
                    </form>
                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Leave Household (non-owner) -->
                @cannot('delete', $household)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form method="POST" action="{{ route('household.leave') }}" onsubmit="return confirm('Haushalt wirklich verlassen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                Haushalt verlassen
                            </button>
                        </form>
                    </div>
                @endcannot

            @else
                <!-- No Household -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Du gehoerst noch keinem Haushalt an. Erstelle einen neuen oder tritt einem bestehenden bei.</p>

                    <!-- Create Household -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Neuen Haushalt erstellen</h3>
                        <form method="POST" action="{{ route('household.store') }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="name" placeholder="Name des Haushalts" required class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                            <button type="submit" class="px-4 py-2 bg-olive-500 text-white rounded-md hover:bg-olive-600 transition">
                                Erstellen
                            </button>
                        </form>
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Join via Code -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Haushalt beitreten</h3>
                        <form method="POST" action="{{ route('household.join-code') }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="invite_code" placeholder="Einladungscode eingeben" required class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                Beitreten
                            </button>
                        </form>
                        @error('invite_code') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
