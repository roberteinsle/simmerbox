<?php

namespace App\Observers;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class RecipeObserver
{
    public function created(Recipe $recipe): void
    {
        $this->syncFts($recipe);
    }

    public function updated(Recipe $recipe): void
    {
        $this->deleteFts($recipe->id);
        $this->syncFts($recipe);
    }

    public function deleted(Recipe $recipe): void
    {
        $this->deleteFts($recipe->id);
    }

    private function syncFts(Recipe $recipe): void
    {
        $ingredientsText = $recipe->ingredients()
            ->pluck('name')
            ->implode(' ');

        DB::statement(
            'INSERT INTO recipes_fts(recipe_id, title, description, ingredients_text) VALUES (?, ?, ?, ?)',
            [$recipe->id, $recipe->title, $recipe->description ?? '', $ingredientsText]
        );
    }

    private function deleteFts(int $recipeId): void
    {
        // Fuer contentless FTS5 mit contentless_delete=1
        DB::statement(
            'DELETE FROM recipes_fts WHERE recipe_id = ?',
            [$recipeId]
        );
    }
}
