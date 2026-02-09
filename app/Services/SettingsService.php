<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 Stunde

    /**
     * Einzelnen Einstellungswert abrufen.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (!isset($settings[$key])) {
            return $default;
        }

        return $settings[$key];
    }

    /**
     * Einstellung setzen.
     */
    public function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key, 2);
        $group = $parts[0] ?? 'general';

        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => json_encode($value),
                'type' => $type,
            ]
        );

        $this->clearCache();
    }

    /**
     * Alle Einstellungen als Key-Value-Array.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(function ($setting) {
                    $value = json_decode($setting->value, true);

                    // Typ-Casting
                    $value = match ($setting->type) {
                        'boolean' => (bool) $value,
                        'integer' => (int) $value,
                        'json' => $value,
                        default => (string) $value,
                    };

                    return [$setting->key => $value];
                })
                ->toArray();
        });
    }

    /**
     * Alle Einstellungen einer Gruppe.
     */
    public function group(string $group): array
    {
        $all = $this->all();
        $prefix = $group . '.';

        return collect($all)
            ->filter(fn ($value, $key) => str_starts_with($key, $prefix))
            ->toArray();
    }

    /**
     * Cache leeren.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
