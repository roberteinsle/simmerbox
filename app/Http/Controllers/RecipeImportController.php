<?php

namespace App\Http\Controllers;

use App\Services\RecipeImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecipeImportController extends Controller
{
    public function __construct(
        protected RecipeImportService $importService
    ) {}

    public function create(): View
    {
        return view('recipes.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'json' => ['required', 'string'],
        ], [
            'json.required' => 'Bitte fuege den JSON-Text ein.',
        ]);

        $recipe = $this->importService->import(
            $request->input('json'),
            auth()->id(),
            auth()->user()->household_id
        );

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Rezept erfolgreich importiert.');
    }
}
