<?php

namespace App\Models;

use App\Traits\ProtectsClosedBatch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EggCollection extends Model
{
    use HasFactory, ProtectsClosedBatch;

    protected $fillable = [
        'collection_number',
        'batch_id',
        'farm_id',
        'collection_date',
        'total_trays',
        'total_eggs',
        'quality_grade',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'total_trays' => 'integer',
            'total_eggs' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->collection_number) {
                $model->collection_number = static::generateCollectionNumber();
            }
        });
    }

    public static function generateCollectionNumber(): string
    {
        $lastCollection = static::latest('id')->first();
        $number = $lastCollection ? ((int) substr($lastCollection->collection_number, 3)) + 1 : 1;

        return 'EGC-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }

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

    public function eggSales(): HasMany
    {
        return $this->hasMany(EggSale::class);
    }
}
