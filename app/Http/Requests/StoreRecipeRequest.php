<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'portions' => ['required', 'integer', 'min:1', 'max:100'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:5120'],
            'tags' => ['nullable', 'string'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.amount' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.source' => ['nullable', 'string', 'in:bio-kiste,einkauf,vorrat'],
            'ingredients.*.note' => ['nullable', 'string', 'max:255'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.instruction' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Bitte gib einen Titel ein.',
            'category_id.required' => 'Bitte waehle eine Kategorie.',
            'category_id.exists' => 'Die gewaehlte Kategorie existiert nicht.',
            'portions.required' => 'Bitte gib die Anzahl der Portionen an.',
            'portions.min' => 'Mindestens 1 Portion.',
            'image.image' => 'Die Datei muss ein Bild sein.',
            'image.max' => 'Das Bild darf maximal 5 MB gross sein.',
            'ingredients.required' => 'Mindestens eine Zutat ist erforderlich.',
            'ingredients.min' => 'Mindestens eine Zutat ist erforderlich.',
            'ingredients.*.name.required' => 'Bitte gib den Namen der Zutat ein.',
            'steps.required' => 'Mindestens ein Zubereitungsschritt ist erforderlich.',
            'steps.min' => 'Mindestens ein Zubereitungsschritt ist erforderlich.',
            'steps.*.instruction.required' => 'Bitte gib die Anweisung fuer diesen Schritt ein.',
        ];
    }
}
