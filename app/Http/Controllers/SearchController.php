<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchService $searchService): View
    {
        $query = $request->input('q', '');
        $categoryId = $request->input('category');
        $tagId = $request->input('tag');

        $results = null;
        if (!empty(trim($query))) {
            $results = $searchService->search(
                $query,
                $categoryId ? (int) $categoryId : null,
                $tagId ? (int) $tagId : null,
            );
        }

        $categories = Category::orderBy('sort_order')->get();
        $tags = Tag::orderBy('name')->get();

        return view('search.index', compact('query', 'results', 'categories', 'tags', 'categoryId', 'tagId'));
    }
}
