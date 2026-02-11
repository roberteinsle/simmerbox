<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecipeImportService
{
    public function import(string $json, int $userId, ?int $householdId): Recipe
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'json' => 'Ungueltiges JSON-Format: ' . json_last_error_msg(),
            ]);
        }

        $this->validate($data);

        return DB::transaction(function () use ($data, $userId, $householdId) {
            $category = $this->resolveCategory($data['category'] ?? null);

            // Support both old and new schema field names
            $portions = $data['portions'] ?? $data['servings'] ?? 4;
            $prepTime = $data['preparation_time'] ?? $data['prep_time_minutes'] ?? $data['total_time_minutes'] ?? null;

            $recipe = Recipe::create([
                'user_id' => $userId,
                'household_id' => $householdId,
                'category_id' => $category->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'] ?? $data['notes'] ?? null,
                'portions' => $portions,
                'preparation_time' => $prepTime,
            ]);

            foreach ($data['ingredients'] as $index => $ingredient) {
                $recipe->ingredients()->create([
                    'name' => $ingredient['name'],
                    'amount' => $ingredient['amount'] ?? null,
                    'unit' => $ingredient['unit'] ?? null,
                    'source' => $ingredient['source'] ?? null,
                    'note' => $ingredient['note'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            foreach ($data['steps'] as $index => $step) {
                $instruction = is_string($step) ? $step : ($step['instruction'] ?? $step);
                $recipe->preparationSteps()->create([
                    'step_number' => $index + 1,
                    'instruction' => $instruction,
                ]);
            }

            if (!empty($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
                $recipe->tags()->sync($tagIds);
            }

            return $recipe;
        });
    }

    protected function validate(array $data): void
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['json'][] = 'Das Feld "title" ist erforderlich.';
        }

        if (empty($data['ingredients']) || !is_array($data['ingredients'])) {
            $errors['json'][] = 'Das Feld "ingredients" muss ein Array mit mindestens einem Eintrag sein.';
        } else {
            foreach ($data['ingredients'] as $i => $ingredient) {
                if (empty($ingredient['name'])) {
                    $errors['json'][] = "Zutat #" . ($i + 1) . ": 'name' ist erforderlich.";
                }
            }
        }

        if (empty($data['steps']) || !is_array($data['steps'])) {
            $errors['json'][] = 'Das Feld "steps" muss ein Array mit mindestens einem Eintrag sein.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function resolveCategory(?string $categoryName): Category
    {
        if (!$categoryName) {
            return Category::where('slug', 'sonstiges')->firstOrFail();
        }

        $category = Category::where('name', $categoryName)
            ->orWhere('slug', Str::slug($categoryName))
            ->first();

        return $category ?? Category::where('slug', 'sonstiges')->firstOrFail();
    }
}
