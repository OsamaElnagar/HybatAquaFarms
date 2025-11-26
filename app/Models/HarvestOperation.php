<?php

namespace App\Models;

use App\Enums\HarvestOperationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class HarvestOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_number',
        'batch_id',
        'farm_id',
        'start_date',
        'end_date',
        'status',
        'estimated_duration_days',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => HarvestOperationStatus::class,
        ];
    }

    /**
     * Boot method for auto-generating operation numbers
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->operation_number) {
                $model->operation_number = static::generateOperationNumber();
            }
        });
    }

    /**
     * Generate unique operation number
     */
    public static function generateOperationNumber(): string
    {
        $lastOperation = static::latest('id')->first();
        $number = $lastOperation ? ((int) substr($lastOperation->operation_number, 4)) + 1 : 1;
        return 'HOP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function harvestBoxes(): HasMany
    {
        return $this->hasMany(HarvestBox::class);
    }

    public function salesOrders(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalesOrder::class,
            HarvestBox::class,
            'harvest_operation_id', // Foreign key on harvest_boxes
            'id',                    // Foreign key on sales_orders
            'id',                    // Local key on harvest_operations
            'sales_order_id'         // Local key on harvest_boxes
        )->distinct();
    }

    // Calculated Attributes
    public function getTotalBoxesAttribute(): int
    {
        return $this->harvestBoxes()->count();
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->harvestBoxes()->sum('weight');
    }

    public function getTotalFishCountAttribute(): int
    {
        return (int) $this->harvestBoxes()->sum('fish_count');
    }

    public function getSoldBoxesCountAttribute(): int
    {
        return $this->harvestBoxes()->where('is_sold', true)->count();
    }

    public function getUnsoldBoxesCountAttribute(): int
    {
        return $this->harvestBoxes()->where('is_sold', false)->count();
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->harvestBoxes()->where('is_sold', true)->sum('subtotal');
    }

    public function getActualDurationAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getDaysRunningAttribute(): int
    {
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInDays($endDate) + 1;
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, [
            HarvestOperationStatus::Ongoing,
            HarvestOperationStatus::Paused
        ]);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === HarvestOperationStatus::Completed;
    }
}
