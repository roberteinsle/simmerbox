<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(SettingsService $settings): View
    {
        $groups = [
            'general' => [
                'label' => 'Allgemein',
                'settings' => [
                    'general.app_name' => ['label' => 'App-Name', 'type' => 'text'],
                    'general.app_description' => ['label' => 'Beschreibung', 'type' => 'text'],
                ],
            ],
            'auth' => [
                'label' => 'Authentifizierung',
                'settings' => [
                    'auth.registration_mode' => ['label' => 'Registrierung', 'type' => 'select', 'options' => ['open' => 'Offen', 'invite_only' => 'Nur mit Einladung']],
                    'auth.allow_password_reset' => ['label' => 'Passwort-Reset erlauben', 'type' => 'boolean'],
                ],
            ],
            'permissions' => [
                'label' => 'Berechtigungen',
                'settings' => [
                    'permissions.recipe_view' => ['label' => 'Rezepte ansehen', 'type' => 'select', 'options' => ['everyone' => 'Alle', 'household' => 'Nur Haushalt', 'owner' => 'Nur Ersteller']],
                    'permissions.recipe_edit' => ['label' => 'Rezepte bearbeiten', 'type' => 'select', 'options' => ['everyone' => 'Alle', 'household' => 'Nur Haushalt', 'owner' => 'Nur Ersteller']],
                    'permissions.recipe_delete' => ['label' => 'Rezepte loeschen', 'type' => 'select', 'options' => ['everyone' => 'Alle', 'household' => 'Nur Haushalt', 'owner' => 'Nur Ersteller']],
                ],
            ],
            'meal_plan' => [
                'label' => 'Wochenplan',
                'settings' => [
                    'meal_plan.default_portions' => ['label' => 'Standard-Portionen', 'type' => 'number'],
                    'meal_plan.week_start_day' => ['label' => 'Wochenstart', 'type' => 'select', 'options' => ['monday' => 'Montag', 'sunday' => 'Sonntag']],
                ],
            ],
            'upload' => [
                'label' => 'Upload',
                'settings' => [
                    'upload.max_image_size_mb' => ['label' => 'Max. Bildgroesse (MB)', 'type' => 'number'],
                ],
            ],
            'mail' => [
                'label' => 'E-Mail',
                'settings' => [
                    'mail.from_address' => ['label' => 'Absender-Adresse', 'type' => 'email'],
                    'mail.from_name' => ['label' => 'Absender-Name', 'type' => 'text'],
                ],
            ],
        ];

        $values = $settings->all();

        return view('admin.settings.index', compact('groups', 'values'));
    }

    public function update(Request $request, SettingsService $settings): RedirectResponse
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            // Konvertiere "settings.key.subkey" zu "key.subkey"
            $settingKey = str_replace('settings_', '', $key);
            $settingKey = str_replace('_', '.', $settingKey);

            // Boolean-Handling: Checkboxen senden nur wenn angehakt
            if ($value === 'on' || $value === '1') {
                $value = true;
            } elseif ($value === '0') {
                $value = false;
            } elseif (is_numeric($value) && str_contains($settingKey, 'size') || str_contains($settingKey, 'portions')) {
                $value = (int) $value;
            }

            $settings->set($settingKey, $value);
        }

        return back()->with('success', 'Einstellungen gespeichert.');
    }
}
