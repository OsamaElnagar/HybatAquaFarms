<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestBox extends Model
{
    /** @use HasFactory<\Database\Factories\HarvestBoxFactory> */
    use HasFactory;

    protected $fillable = [
        'harvest_id',
        'box_number',
        'weight',
        'fish_count',
        'average_fish_weight',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:3',
            'average_fish_weight' => 'decimal:3',
        ];
    }

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }
}
