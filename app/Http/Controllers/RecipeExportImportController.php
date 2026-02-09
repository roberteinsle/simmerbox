<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class RecipeExportImportController extends Controller
{
    public function export(): BinaryFileResponse
    {
        $user = auth()->user();
        $recipes = Recipe::where('user_id', $user->id)
            ->with(['category', 'tags', 'ingredients' => fn ($q) => $q->orderBy('sort_order'), 'preparationSteps' => fn ($q) => $q->orderBy('step_number')])
            ->get();

        $tempFile = tempnam(sys_get_temp_dir(), 'simmerbox_export_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $exportData = [];

        foreach ($recipes as $recipe) {
            $recipeData = [
                'title' => $recipe->title,
                'description' => $recipe->description,
                'category' => $recipe->category->name,
                'portions' => $recipe->portions,
                'preparation_time' => $recipe->preparation_time,
                'tags' => $recipe->tags->pluck('name')->toArray(),
                'ingredients' => $recipe->ingredients->map(fn ($i) => [
                    'name' => $i->name,
                    'amount' => $i->amount,
                    'unit' => $i->unit,
                ])->toArray(),
                'steps' => $recipe->preparationSteps->map(fn ($s) => [
                    'instruction' => $s->instruction,
                ])->toArray(),
                'image' => null,
            ];

            if ($recipe->image_path && Storage::disk('public')->exists($recipe->image_path)) {
                $imageFilename = Str::slug($recipe->title) . '.' . pathinfo($recipe->image_path, PATHINFO_EXTENSION);
                $imagePath = 'images/' . $imageFilename;
                $recipeData['image'] = $imagePath;

                $zip->addFromString($imagePath, Storage::disk('public')->get($recipe->image_path));

                // Also export thumbnail if exists
                $thumbPath = dirname($recipe->image_path) . '/thumbs/' . basename($recipe->image_path);
                if (Storage::disk('public')->exists($thumbPath)) {
                    $thumbFilename = 'images/thumbs/' . $imageFilename;
                    $zip->addFromString($thumbFilename, Storage::disk('public')->get($thumbPath));
                }
            }

            $exportData[] = $recipeData;
        }

        $zip->addFromString('recipes.json', json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        return response()->download($tempFile, 'simmerbox-export-' . date('Y-m-d') . '.zip')
            ->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'zip_file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ], [
            'zip_file.required' => 'Bitte waehle eine ZIP-Datei aus.',
            'zip_file.mimes' => 'Die Datei muss eine ZIP-Datei sein.',
            'zip_file.max' => 'Die Datei darf maximal 50MB gross sein.',
        ]);

        $zip = new ZipArchive();
        $tempDir = sys_get_temp_dir() . '/simmerbox_import_' . uniqid();

        if ($zip->open($request->file('zip_file')->getPathname()) !== true) {
            return back()->withErrors(['zip_file' => 'Die ZIP-Datei konnte nicht geoeffnet werden.']);
        }

        $zip->extractTo($tempDir);
        $zip->close();

        $jsonPath = $tempDir . '/recipes.json';
        if (!file_exists($jsonPath)) {
            $this->cleanupTempDir($tempDir);
            return back()->withErrors(['zip_file' => 'Die ZIP-Datei enthaelt keine recipes.json.']);
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($data)) {
            $this->cleanupTempDir($tempDir);
            return back()->withErrors(['zip_file' => 'Die recipes.json ist ungueltig.']);
        }

        $user = auth()->user();
        $imported = 0;

        DB::transaction(function () use ($data, $user, $tempDir, &$imported) {
            foreach ($data as $recipeData) {
                if (empty($recipeData['title'])) {
                    continue;
                }

                $category = $this->resolveCategory($recipeData['category'] ?? null);

                $recipe = Recipe::create([
                    'user_id' => $user->id,
                    'household_id' => $user->household_id,
                    'category_id' => $category->id,
                    'title' => $recipeData['title'],
                    'slug' => Str::slug($recipeData['title']),
                    'description' => $recipeData['description'] ?? null,
                    'portions' => $recipeData['portions'] ?? 4,
                    'preparation_time' => $recipeData['preparation_time'] ?? null,
                ]);

                // Import image
                if (!empty($recipeData['image'])) {
                    $imageSrcPath = $tempDir . '/' . $recipeData['image'];
                    if (file_exists($imageSrcPath)) {
                        $ext = pathinfo($imageSrcPath, PATHINFO_EXTENSION) ?: 'jpg';
                        $filename = uniqid() . '_' . time() . '.' . $ext;
                        $storagePath = 'recipes/' . $filename;

                        Storage::disk('public')->put($storagePath, file_get_contents($imageSrcPath));

                        // Import thumbnail if available
                        $thumbSrcPath = $tempDir . '/images/thumbs/' . basename($recipeData['image']);
                        if (file_exists($thumbSrcPath)) {
                            Storage::disk('public')->put('recipes/thumbs/' . $filename, file_get_contents($thumbSrcPath));
                        }

                        $recipe->update(['image_path' => $storagePath]);
                    }
                }

                // Ingredients
                foreach ($recipeData['ingredients'] ?? [] as $index => $ingredient) {
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

                // Steps
                foreach ($recipeData['steps'] ?? [] as $index => $step) {
                    $instruction = is_string($step) ? $step : ($step['instruction'] ?? '');
                    if (empty($instruction)) {
                        continue;
                    }
                    $recipe->preparationSteps()->create([
                        'step_number' => $index + 1,
                        'instruction' => $instruction,
                    ]);
                }

                // Tags
                if (!empty($recipeData['tags']) && is_array($recipeData['tags'])) {
                    $tagIds = [];
                    foreach ($recipeData['tags'] as $tagName) {
                        if (empty($tagName)) {
                            continue;
                        }
                        $tag = Tag::firstOrCreate(
                            ['slug' => Str::slug($tagName)],
                            ['name' => $tagName]
                        );
                        $tagIds[] = $tag->id;
                    }
                    $recipe->tags()->sync($tagIds);
                }

                $imported++;
            }
        });

        $this->cleanupTempDir($tempDir);

        return redirect()->route('profile.edit')
            ->with('success', $imported . ' Rezept(e) erfolgreich importiert.');
    }

    private function resolveCategory(?string $categoryName): Category
    {
        if (!$categoryName) {
            return Category::where('slug', 'sonstiges')->firstOrFail();
        }

        $category = Category::where('name', $categoryName)
            ->orWhere('slug', Str::slug($categoryName))
            ->first();

        return $category ?? Category::where('slug', 'sonstiges')->firstOrFail();
    }

    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
