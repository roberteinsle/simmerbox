<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            DefaultSettingsSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@simmerbox.local',
            'is_admin' => true,
        ]);

        User::where('email', 'robert@einsle.com')->update(['is_admin' => true]);
    }
}
