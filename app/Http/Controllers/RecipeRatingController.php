<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeRatingController extends Controller
{
    public function store(Request $request, Recipe $recipe): JsonResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        RecipeRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'recipe_id' => $recipe->id,
            ],
            [
                'rating' => $request->integer('rating'),
            ]
        );

        $avg = RecipeRating::where('recipe_id', $recipe->id)->avg('rating');
        $count = RecipeRating::where('recipe_id', $recipe->id)->count();

        return response()->json([
            'average' => round($avg, 1),
            'count' => $count,
            'userRating' => $request->integer('rating'),
        ]);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        RecipeRating::where('user_id', auth()->id())
            ->where('recipe_id', $recipe->id)
            ->delete();

        $avg = RecipeRating::where('recipe_id', $recipe->id)->avg('rating');
        $count = RecipeRating::where('recipe_id', $recipe->id)->count();

        return response()->json([
            'average' => $avg ? round($avg, 1) : null,
            'count' => $count,
            'userRating' => null,
        ]);
    }
}
