<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Observers\FarmUnitObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([FarmUnitObserver::class])]
class FarmUnit extends Model
{
    /** @use HasFactory<\Database\Factories\FarmUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'code',
        'name',
        'unit_type',
        'capacity',
        'status',
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

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'unit_id');
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class, 'unit_id');
    }

    /**
     * Get total feed consumed by this unit in a date range.
     */
    public function getTotalFeedConsumed(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->dailyFeedIssues();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return (float) $query->sum('quantity');
    }

    /**
     * Get active batches in this unit.
     */
    public function getActiveBatchesAttribute()
    {
        return $this->batches()->where('status', 'active')->get();
    }

    /**
     * Get total current stock in this unit.
     */
    public function getTotalCurrentStockAttribute(): int
    {
        return (int) $this->batches()->where('status', 'active')->sum('current_quantity');
    }
}
