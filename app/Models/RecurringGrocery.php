<?php

namespace App\Models;

use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringGrocery extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'name',
        'amount',
        'unit',
        'frequency_type',
        'frequency_day',
        'frequency_interval',
        'raw_input',
        'next_occurrence',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_occurrence' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
