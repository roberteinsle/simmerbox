<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('recipes')
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ], [
            'name.required' => 'Bitte gib einen Namen ein.',
            'name.unique' => 'Diese Kategorie existiert bereits.',
        ]);

        $maxSort = Category::max('sort_order') ?? -1;

        Category::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'sort_order' => $maxSort + 1,
        ]);

        return back()->with('success', 'Kategorie erstellt.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', "unique:categories,name,{$category->id}"],
        ]);

        $category->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        return back()->with('success', 'Kategorie aktualisiert.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->recipes()->count() > 0) {
            return back()->with('error', "Kategorie \"{$category->name}\" kann nicht geloescht werden, da sie noch {$category->recipes()->count()} Rezepte enthaelt.");
        }

        $category->delete();

        return back()->with('success', 'Kategorie geloescht.');
    }
}
