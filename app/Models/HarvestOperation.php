<?php

namespace App\Models;

use App\Enums\HarvestOperationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class HarvestOperation extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'operation_number',
        'batch_id',
        'farm_id',
        'start_date',
        'end_date',
        'status',
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
            if (! $model->operation_number) {
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

        return 'HOP-'.str_pad($number, 4, '0', STR_PAD_LEFT);
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function getTotalBoxesAttribute(): int
    {
        return (int) $this->orderItems()->sum('quantity');
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->orderItems()->sum('total_weight');
    }

    public function getFullDisplayNameAttribute(): string
    {
        /** @var \Carbon\Carbon|null $startDate */
        $startDate = $this->start_date;
        $date = $startDate?->format('Y-m-d') ?? '—';

        return "{$this->operation_number} - {$this->farm->name} - ({$date})";
    }
}
