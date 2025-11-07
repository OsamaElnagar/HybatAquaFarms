<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmUnit extends Model
{
    /** @use HasFactory<\Database\Factories\FarmUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'code',
        'unit_type',
        'capacity',
        'status',
        'current_stock_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_type' => UnitType::class,
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function currentStock(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'current_stock_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'unit_id');
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class, 'unit_id');
    }
}
