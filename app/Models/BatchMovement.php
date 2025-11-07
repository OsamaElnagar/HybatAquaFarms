<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchMovement extends Model
{
    /** @use HasFactory<\Database\Factories\BatchMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'movement_type',
        'from_farm_id',
        'to_farm_id',
        'from_unit_id',
        'to_unit_id',
        'quantity',
        'weight',
        'date',
        'reason',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:3',
            'movement_type' => MovementType::class,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function fromFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'from_farm_id');
    }

    public function toFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'to_farm_id');
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, 'to_unit_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
