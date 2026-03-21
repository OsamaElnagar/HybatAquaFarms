<?php

namespace App\Models;

use Database\Factories\ProductionRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    /** @use HasFactory<ProductionRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'farm_id',
        'unit_id',
        'date',
        'quantity',
        'unit',
        'weight',
        'quality_grade',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
            'weight' => 'decimal:3',
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
