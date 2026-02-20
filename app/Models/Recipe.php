<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (Recipe $recipe) {
            if (!$recipe->id) {
                $recipe->id = (int) (microtime(true) * 1000);
            }
        });
    }

    protected $fillable = [
        'household_id',
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'portions',
        'preparation_time',
        'image_path',
        'bio_kiste_date',
    ];

    protected function casts(): array
    {
        return [
            'bio_kiste_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function preparationSteps(): HasMany
    {
        return $this->hasMany(PreparationStep::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }
}
