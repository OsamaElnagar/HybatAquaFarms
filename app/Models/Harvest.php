<?php

namespace App\Models;

use App\Enums\HarvestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Harvest extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن إضافة حصاد لدورة مقفلة");
            }

            // Auto-generate harvest number
            if (!$model->harvest_number) {
                $model->harvest_number = static::generateHarvestNumber();
            }
        });

        static::updating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن تعديل حصاد دورة مقفلة");
            }
        });

        static::deleting(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن حذف حصاد دورة مقفلة");
            }
        });
    }

    /**
     * Generate unique harvest number
     */
    public static function generateHarvestNumber(): string
    {
        $lastHarvest = static::latest('id')->first();
        $number = $lastHarvest ? ((int) substr($lastHarvest->harvest_number, 2)) + 1 : 1;
        return 'H-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        "harvest_number",
        "harvest_operation_id",
        "batch_id",
        "farm_id",
        "harvest_date",
        "shift",
        "status",
        "recorded_by",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "harvest_date" => "date",
            "status" => HarvestStatus::class,
        ];
    }

    // Relationships
    public function harvestOperation(): BelongsTo
    {
        return $this->belongsTo(HarvestOperation::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "recorded_by");
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(HarvestBox::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(FarmUnit::class, 'harvest_units', 'harvest_id', 'unit_id')
            ->withPivot([
                'fish_count_before',
                'fish_count_harvested',
                'fish_count_remaining',
                'percentage_harvested',
                'notes'
            ])
            ->withTimestamps()
            ->using(HarvestUnit::class);
    }

    public function harvestUnits(): HasMany
    {
        return $this->hasMany(HarvestUnit::class);
    }

    // Calculated Attributes - from boxes
    public function getTotalBoxesAttribute(): int
    {
        return $this->boxes()->count();
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->boxes()->sum('weight');
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->boxes()->sum('fish_count');
    }

    public function getAverageWeightPerBoxAttribute(): ?float
    {
        $count = $this->total_boxes;
        if ($count == 0) return null;
        return $this->total_weight / $count;
    }

    public function getAverageFishWeightAttribute(): ?float
    {
        $count = $this->total_quantity;
        if ($count == 0) return null;
        // Convert to grams
        return ($this->total_weight * 1000) / $count;
    }

    public function getSoldBoxesCountAttribute(): int
    {
        return $this->boxes()->where('is_sold', true)->count();
    }

    public function getUnsoldBoxesCountAttribute(): int
    {
        return $this->boxes()->where('is_sold', false)->count();
    }
}
