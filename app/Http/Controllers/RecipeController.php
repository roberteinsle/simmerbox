<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\Tag;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function index(Request $request): View
    {
        $query = Recipe::with(['category', 'tags', 'user'])
            ->latest();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag));
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $recipes = $query->paginate(12);
        $categories = Category::orderBy('sort_order')->get();
        $tags = Tag::orderBy('name')->get();

        return view('recipes.index', compact('recipes', 'categories', 'tags'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('recipes.create', compact('categories'));
    }

    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $request) {
            $recipe = Recipe::create([
                'user_id' => auth()->id(),
                'household_id' => auth()->user()->household_id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'description' => $validated['description'] ?? null,
                'portions' => $validated['portions'],
                'preparation_time' => $validated['preparation_time'] ?? null,
            ]);

            if ($request->hasFile('image')) {
                $recipe->update([
                    'image_path' => $this->imageService->store($request->file('image')),
                ]);
            }

            $this->syncIngredients($recipe, $validated['ingredients']);
            $this->syncSteps($recipe, $validated['steps']);
            $this->syncTags($recipe, $validated['tags'] ?? '');

            return redirect()->route('recipes.show', $recipe)
                ->with('success', 'Rezept erfolgreich erstellt.');
        });
    }

    public function show(Recipe $recipe): View
    {
        $this->authorize('view', $recipe);

        $recipe->load(['category', 'tags', 'user', 'ingredients' => fn ($q) => $q->orderBy('sort_order'), 'preparationSteps' => fn ($q) => $q->orderBy('step_number')]);

        return view('recipes.show', compact('recipe'));
    }

    public function edit(Recipe $recipe): View
    {
        $this->authorize('update', $recipe);

        $recipe->load(['ingredients' => fn ($q) => $q->orderBy('sort_order'), 'preparationSteps' => fn ($q) => $q->orderBy('step_number'), 'tags']);
        $categories = Category::orderBy('sort_order')->get();

        return view('recipes.edit', compact('recipe', 'categories'));
    }

    public function update(UpdateRecipeRequest $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('update', $recipe);

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $request, $recipe) {
            $recipe->update([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'description' => $validated['description'] ?? null,
                'portions' => $validated['portions'],
                'preparation_time' => $validated['preparation_time'] ?? null,
            ]);

            if ($request->boolean('remove_image') && $recipe->image_path) {
                $this->imageService->delete($recipe->image_path);
                $recipe->update(['image_path' => null]);
            }

            if ($request->hasFile('image')) {
                $this->imageService->delete($recipe->image_path);
                $recipe->update([
                    'image_path' => $this->imageService->store($request->file('image')),
                ]);
            }

            $this->syncIngredients($recipe, $validated['ingredients']);
            $this->syncSteps($recipe, $validated['steps']);
            $this->syncTags($recipe, $validated['tags'] ?? '');

            return redirect()->route('recipes.show', $recipe)
                ->with('success', 'Rezept erfolgreich aktualisiert.');
        });
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $this->authorize('delete', $recipe);

        $this->imageService->delete($recipe->image_path);
        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Rezept erfolgreich geloescht.');
    }

    protected function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        $recipe->ingredients()->delete();

        foreach ($ingredients as $index => $ingredient) {
            if (empty($ingredient['name'])) {
                continue;
            }
            $recipe->ingredients()->create([
                'name' => $ingredient['name'],
                'amount' => $ingredient['amount'] ?? null,
                'unit' => $ingredient['unit'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    protected function syncSteps(Recipe $recipe, array $steps): void
    {
        $recipe->preparationSteps()->delete();

        foreach ($steps as $index => $step) {
            if (empty($step['instruction'])) {
                continue;
            }
            $recipe->preparationSteps()->create([
                'step_number' => $index + 1,
                'instruction' => $step['instruction'],
            ]);
        }
    }

    protected function syncTags(Recipe $recipe, string $tagString): void
    {
        if (empty($tagString)) {
            $recipe->tags()->sync([]);
            return;
        }

        $tagNames = array_map('trim', explode(',', $tagString));
        $tagIds = [];

        foreach ($tagNames as $name) {
            if (empty($name)) {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            $tagIds[] = $tag->id;
        }

        $recipe->tags()->sync($tagIds);
    }
}
