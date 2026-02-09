@props(['recipe' => null, 'categories'])

@php
    $existingIngredients = old('ingredients', $recipe?->ingredients?->map(fn($i) => ['name' => $i->name, 'amount' => $i->amount, 'unit' => $i->unit])->toArray() ?? [['name' => '', 'amount' => '', 'unit' => '']]);
    $existingSteps = old('steps', $recipe?->preparationSteps?->map(fn($s) => ['instruction' => $s->instruction])->toArray() ?? [['instruction' => '']]);
    $existingTags = old('tags', $recipe?->tags?->pluck('name')->implode(', ') ?? '');
@endphp

<div x-data="{
    ingredients: {{ json_encode($existingIngredients) }},
    steps: {{ json_encode($existingSteps) }},
    addIngredient() {
        this.ingredients.push({ name: '', amount: '', unit: '' });
    },
    removeIngredient(index) {
        if (this.ingredients.length > 1) this.ingredients.splice(index, 1);
    },
    addStep() {
        this.steps.push({ instruction: '' });
    },
    removeStep(index) {
        if (this.steps.length > 1) this.steps.splice(index, 1);
    }
}">
    <!-- Title -->
    <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titel *</label>
        <input type="text" name="title" id="title" value="{{ old('title', $recipe?->title) }}" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
        @error('title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <!-- Description -->
    <div class="mb-4">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Beschreibung</label>
        <textarea name="description" id="description" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">{{ old('description', $recipe?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <!-- Category + Portions + Time -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorie *</label>
            <select name="category_id" id="category_id" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                <option value="">Bitte waehlen...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $recipe?->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="portions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Portionen *</label>
            <input type="number" name="portions" id="portions" value="{{ old('portions', $recipe?->portions ?? 4) }}" min="1" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
            @error('portions') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="preparation_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zubereitungszeit (Min.)</label>
            <input type="number" name="preparation_time" id="preparation_time" value="{{ old('preparation_time', $recipe?->preparation_time) }}" min="1" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
            @error('preparation_time') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Tags -->
    <div class="mb-4">
        <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags (kommagetrennt)</label>
        <input type="text" name="tags" id="tags" value="{{ $existingTags }}" placeholder="z.B. vegetarisch, schnell, italienisch" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
        @error('tags') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <!-- Ingredients -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Zutaten *</h3>
            <button type="button" @click="addIngredient()" class="text-sm text-olive-500 hover:text-olive-600">+ Zutat hinzufuegen</button>
        </div>
        @error('ingredients') <p class="mb-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        <template x-for="(ingredient, index) in ingredients" :key="index">
            <div class="flex gap-2 mb-2 items-start">
                <div class="w-20">
                    <input type="number" :name="'ingredients[' + index + '][amount]'" x-model="ingredient.amount" step="0.01" min="0" placeholder="Menge" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                </div>
                <div class="w-20">
                    <input type="text" :name="'ingredients[' + index + '][unit]'" x-model="ingredient.unit" placeholder="Einheit" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                </div>
                <div class="flex-1">
                    <input type="text" :name="'ingredients[' + index + '][name]'" x-model="ingredient.name" placeholder="Zutat" required class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500">
                </div>
                <button type="button" @click="removeIngredient(index)" x-show="ingredients.length > 1" class="p-2 text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Preparation Steps -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Zubereitungsschritte *</h3>
            <button type="button" @click="addStep()" class="text-sm text-olive-500 hover:text-olive-600">+ Schritt hinzufuegen</button>
        </div>
        @error('steps') <p class="mb-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        <template x-for="(step, index) in steps" :key="index">
            <div class="flex gap-2 mb-3 items-start">
                <span class="flex-shrink-0 w-8 h-8 bg-olive-500 text-white rounded-full flex items-center justify-center font-semibold text-sm mt-1" x-text="index + 1"></span>
                <div class="flex-1">
                    <textarea :name="'steps[' + index + '][instruction]'" x-model="step.instruction" rows="2" placeholder="Beschreibe diesen Schritt..." required class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-olive-500 focus:ring-olive-500"></textarea>
                </div>
                <button type="button" @click="removeStep(index)" x-show="steps.length > 1" class="p-2 text-red-500 hover:text-red-700 mt-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Image Upload -->
    <div class="mb-4 border-t border-gray-200 dark:border-gray-700 pt-4" x-data="{
        dragging: false,
        fileName: null,
        preview: null,
        handleFiles(files) {
            if (files.length === 0) return;
            const file = files[0];
            if (!file.type.startsWith('image/')) return;
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => this.preview = e.target.result;
            reader.readAsDataURL(file);
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.fileInput.files = dt.files;
        }
    }">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Rezeptbild</label>

        @if($recipe?->image_path)
            <div class="mb-3 flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <img src="{{ asset('storage/' . dirname($recipe->image_path) . '/thumbs/' . basename($recipe->image_path)) }}" alt="Aktuelles Bild" class="w-20 h-20 object-cover rounded-lg">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Aktuelles Bild</p>
                    <label class="flex items-center gap-2 text-sm text-red-500 dark:text-red-400 mt-1 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 dark:border-gray-600 text-red-500 focus:ring-red-500">
                        Bild entfernen
                    </label>
                </div>
            </div>
        @endif

        <div
            class="relative border-2 border-dashed rounded-lg py-4 px-4 text-center transition-colors cursor-pointer"
            :class="dragging ? 'border-olive-500 bg-olive-50 dark:bg-olive-950' : 'border-gray-300 dark:border-gray-600 hover:border-olive-400 dark:hover:border-olive-600'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
            @click="$refs.fileInput.click()"
        >
            <input type="file" name="image" x-ref="fileInput" accept="image/*" class="hidden" @change="handleFiles($event.target.files)">

            <template x-if="preview">
                <div>
                    <img :src="preview" class="mx-auto max-h-40 rounded-lg mb-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="fileName"></p>
                </div>
            </template>

            <template x-if="!preview">
                <div>
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500 mb-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <p class="text-sm text-olive-600 dark:text-olive-400 font-medium">Klicken oder Bild hierher ziehen</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">JPEG, PNG, GIF oder WebP. Max. {{ settings('upload.max_image_size_mb', 5) }}MB</p>
                </div>
            </template>
        </div>
        @error('image') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>
</div>
