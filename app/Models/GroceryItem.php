<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroceryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grocery_list_id',
        'name',
        'amount',
        'unit',
        'is_checked',
        'is_manual',
        'source_recipe_ids',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_checked' => 'boolean',
            'is_manual' => 'boolean',
            'source_recipe_ids' => 'array',
        ];
    }

    public function groceryList(): BelongsTo
    {
        return $this->belongsTo(GroceryList::class);
    }
}
