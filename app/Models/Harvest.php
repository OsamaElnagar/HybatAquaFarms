<?php

namespace App\Models;

use App\Enums\HarvestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Harvest extends Model
{
    /** @use HasFactory<\Database\Factories\HarvestFactory> */
    use HasFactory;

    protected $fillable = [
        'harvest_number',
        'batch_id',
        'farm_id',
        'unit_id',
        'sales_order_id',
        'harvest_date',
        'boxes_count',
        'total_weight',
        'average_weight_per_box',
        'total_quantity',
        'average_fish_weight',
        'status',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'total_weight' => 'decimal:3',
            'average_weight_per_box' => 'decimal:3',
            'average_fish_weight' => 'decimal:3',
            'status' => HarvestStatus::class,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, 'unit_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(HarvestBox::class);
    }
}
