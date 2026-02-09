<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIRTUAL TABLE IF NOT EXISTS recipes_fts USING fts5(
                recipe_id UNINDEXED,
                title,
                description,
                ingredients_text,
                content='',
                contentless_delete=1,
                tokenize='unicode61'
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS recipes_fts');
    }
};
