<?php

namespace App\Models;

use App\Observers\HarvestUnitObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([HarvestUnitObserver::class])]
class HarvestUnit extends Model
{
    protected $fillable = [
        'harvest_id',
        'unit_id',
        'fish_count_before',
        'fish_count_harvested',
        'fish_count_remaining',
        'percentage_harvested',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'percentage_harvested' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            // Auto-calculate percentage if we have before count
            if ($model->fish_count_before && $model->fish_count_harvested) {
                $model->percentage_harvested = ($model->fish_count_harvested / $model->fish_count_before) * 100;
            }

            // Auto-calculate remaining
            if ($model->fish_count_before && $model->fish_count_harvested) {
                $model->fish_count_remaining = $model->fish_count_before - $model->fish_count_harvested;
            }
        });
    }

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, 'unit_id');
    }
}
