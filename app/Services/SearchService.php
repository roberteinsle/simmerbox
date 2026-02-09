<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Volltextsuche mit optionalen Kategorie- und Tag-Filtern.
     */
    public function search(
        string $query,
        ?int $categoryId = null,
        ?int $tagId = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        // FTS5-Query aufbereiten: Sonderzeichen escapen und * fuer Prefix-Suche
        $ftsQuery = $this->buildFtsQuery($query);

        if (empty($ftsQuery)) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        // Recipe IDs aus FTS5 holen (mit BM25 Ranking)
        $ftsResults = DB::select(
            'SELECT recipe_id, bm25(recipes_fts, 0, 10.0, 5.0, 3.0) as rank FROM recipes_fts WHERE recipes_fts MATCH ? ORDER BY rank',
            [$ftsQuery]
        );

        $recipeIds = collect($ftsResults)->pluck('recipe_id')->toArray();

        if (empty($recipeIds)) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        // Recipes mit Filtern laden
        $recipesQuery = Recipe::whereIn('id', $recipeIds)
            ->with(['category', 'tags', 'user']);

        if ($categoryId) {
            $recipesQuery->where('category_id', $categoryId);
        }

        if ($tagId) {
            $recipesQuery->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
        }

        // Reihenfolge nach FTS-Ranking beibehalten (SQLite-kompatibel)
        $placeholders = implode(',', array_fill(0, count($recipeIds), '?'));
        $caseStatements = [];
        foreach ($recipeIds as $index => $id) {
            $caseStatements[] = "WHEN {$id} THEN {$index}";
        }
        $orderCase = 'CASE id ' . implode(' ', $caseStatements) . ' END';
        $recipesQuery->orderByRaw($orderCase);

        return $recipesQuery->paginate($perPage)->withQueryString();
    }

    /**
     * Baut einen FTS5-kompatiblen Query-String.
     */
    private function buildFtsQuery(string $query): string
    {
        $query = trim($query);

        if (empty($query)) {
            return '';
        }

        // Sonderzeichen entfernen die FTS5 stoeren
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $query);
        $terms = preg_split('/\s+/', trim($cleaned));
        $terms = array_filter($terms);

        if (empty($terms)) {
            return '';
        }

        // Prefix-Suche: jedes Wort mit * am Ende fuer partielle Treffer
        $ftsTerms = array_map(fn ($term) => '"' . $term . '"*', $terms);

        return implode(' ', $ftsTerms);
    }

    /**
     * Kompletter Neuaufbau des FTS-Index.
     */
    public function reindexAll(): int
    {
        // Alles loeschen
        DB::statement("DELETE FROM recipes_fts");

        // Alle Rezepte mit Zutaten laden und indizieren
        $count = 0;
        Recipe::with('ingredients')->chunk(100, function ($recipes) use (&$count) {
            foreach ($recipes as $recipe) {
                $ingredientsText = $recipe->ingredients->pluck('name')->implode(' ');

                DB::statement(
                    'INSERT INTO recipes_fts(recipe_id, title, description, ingredients_text) VALUES (?, ?, ?, ?)',
                    [$recipe->id, $recipe->title, $recipe->description ?? '', $ingredientsText]
                );

                $count++;
            }
        });

        return $count;
    }
}
