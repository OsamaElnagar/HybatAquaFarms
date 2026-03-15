<?php

namespace App\Models;

use App\Observers\BatchFishObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(BatchFishObserver::class)]
class BatchFish extends Model
{
    protected $table = 'batch_fish';

    protected $fillable = [
        'batch_id',
        'species_id',
        'factory_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }
}
