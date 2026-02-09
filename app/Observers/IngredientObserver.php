<?php

namespace App\Observers;

use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

class IngredientObserver
{
    public function created(Ingredient $ingredient): void
    {
        $this->resyncRecipe($ingredient->recipe_id);
    }

    public function updated(Ingredient $ingredient): void
    {
        $this->resyncRecipe($ingredient->recipe_id);
    }

    public function deleted(Ingredient $ingredient): void
    {
        $this->resyncRecipe($ingredient->recipe_id);
    }

    private function resyncRecipe(int $recipeId): void
    {
        $recipe = \App\Models\Recipe::find($recipeId);
        if (!$recipe) {
            return;
        }

        // Alten FTS-Eintrag loeschen
        DB::statement('DELETE FROM recipes_fts WHERE recipe_id = ?', [$recipeId]);

        // Neu einfuegen
        $ingredientsText = $recipe->ingredients()->pluck('name')->implode(' ');

        DB::statement(
            'INSERT INTO recipes_fts(recipe_id, title, description, ingredients_text) VALUES (?, ?, ?, ?)',
            [$recipe->id, $recipe->title, $recipe->description ?? '', $ingredientsText]
        );
    }
}
