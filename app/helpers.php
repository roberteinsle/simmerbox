<?php

use App\Services\SettingsService;

if (!function_exists('settings')) {
    /**
     * Globale Helper-Funktion fuer Einstellungen.
     *
     * settings('key') - Wert abrufen
     * settings('key', 'default') - Wert mit Fallback
     * settings() - SettingsService-Instanz
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}
