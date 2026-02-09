<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Vorspeise',
            'Hauptgericht',
            'Dessert',
            'Snack',
            'Getraenk',
            'Beilage',
            'Salat',
            'Suppe',
            'Brot & Gebaeck',
            'Sonstiges',
        ];

        foreach ($categories as $index => $name) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
