<?php

namespace App\Models;

use App\Enums\HarvestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Harvest extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->loadMissing('harvestOperation.batch');
            if ($model->harvestOperation?->batch?->is_cycle_closed) {
                throw new \Exception('لا يمكن إضافة حصاد لدورة مقفلة');
            }

            // Auto-generate harvest number
            if (! $model->harvest_number) {
                $model->harvest_number = static::generateHarvestNumber();
            }
        });

        static::updating(function ($model) {
            $model->loadMissing('harvestOperation.batch');
            if ($model->harvestOperation?->batch?->is_cycle_closed) {
                throw new \Exception('لا يمكن تعديل حصاد دورة مقفلة');
            }
        });

        static::deleting(function ($model) {
            $model->loadMissing('harvestOperation.batch');
            if ($model->harvestOperation?->batch?->is_cycle_closed) {
                throw new \Exception('لا يمكن حذف حصاد دورة مقفلة');
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

        return 'H-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'harvest_number',
        'harvest_operation_id',
        'harvest_date',
        'shift',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'status' => HarvestStatus::class,
        ];
    }

    // Relationships
    public function harvestOperation(): BelongsTo
    {
        return $this->belongsTo(HarvestOperation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
