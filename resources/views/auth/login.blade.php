<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('error'))
        <div class="mb-4 text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-Mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Passwort" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-olive-500 shadow-sm focus:ring-olive-500" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Angemeldet bleiben</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <div>
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-olive-500" href="{{ route('password.request') }}">
                        Passwort vergessen?
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @php
                    $registrationOpen = true;
                    try { $registrationOpen = settings('auth.registration_mode', 'open') !== 'invite_only'; } catch (\Exception $e) {}
                @endphp
                @if($registrationOpen && Route::has('register'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100" href="{{ route('register') }}">
                        Registrieren
                    </a>
                @endif

                <x-primary-button>
                    Anmelden
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
