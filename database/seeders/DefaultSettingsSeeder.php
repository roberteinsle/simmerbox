<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'general.app_name', 'value' => json_encode('Simmerbox'), 'type' => 'string'],
            ['group' => 'general', 'key' => 'general.app_description', 'value' => json_encode('Rezeptverwaltung'), 'type' => 'string'],

            // Auth
            ['group' => 'auth', 'key' => 'auth.registration_mode', 'value' => json_encode('open'), 'type' => 'string'],
            ['group' => 'auth', 'key' => 'auth.allow_password_reset', 'value' => json_encode(true), 'type' => 'boolean'],

            // Appearance
            ['group' => 'appearance', 'key' => 'appearance.default_theme', 'value' => json_encode('system'), 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'appearance.primary_color', 'value' => json_encode('#f97316'), 'type' => 'string'],

            // Permissions
            ['group' => 'permissions', 'key' => 'permissions.recipe_view', 'value' => json_encode('everyone'), 'type' => 'string'],
            ['group' => 'permissions', 'key' => 'permissions.recipe_edit', 'value' => json_encode('household'), 'type' => 'string'],
            ['group' => 'permissions', 'key' => 'permissions.recipe_delete', 'value' => json_encode('household'), 'type' => 'string'],

            // Meal Plan
            ['group' => 'meal_plan', 'key' => 'meal_plan.default_portions', 'value' => json_encode(4), 'type' => 'integer'],
            ['group' => 'meal_plan', 'key' => 'meal_plan.week_start_day', 'value' => json_encode('monday'), 'type' => 'string'],

            // Upload
            ['group' => 'upload', 'key' => 'upload.max_image_size_mb', 'value' => json_encode(5), 'type' => 'integer'],

            // Mail
            ['group' => 'mail', 'key' => 'mail.from_address', 'value' => json_encode('hello@example.com'), 'type' => 'string'],
            ['group' => 'mail', 'key' => 'mail.from_name', 'value' => json_encode('Simmerbox'), 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
