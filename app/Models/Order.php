<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'harvest_operation_id',
        'harvest_id',
        'trader_id',
        'driver_id',
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->code)) {
                $order->code = static::generateOrderCode();
            }
        });
    }

    protected static function generateOrderCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'ORD';

        // Get the last order code for today
        $lastOrder = static::whereDate('created_at', '=', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->code, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
    }

    public function harvestOperation(): BelongsTo
    {
        return $this->belongsTo(HarvestOperation::class);
    }

    public function salesOrders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SalesOrder::class);
    }

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(Trader::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->items->sum('total_weight') ?? 0;
    }

    public function getTotalBoxesAttribute(): int
    {
        return $this->items->sum('quantity') ?? 0;
    }

    public function farm(): HasOneThrough
    {
        return $this->hasOneThrough(Farm::class, HarvestOperation::class, 'id', 'id', 'harvest_operation_id', 'farm_id');
    }

    public function batch(): HasOneThrough
    {
        return $this->hasOneThrough(Batch::class, HarvestOperation::class, 'id', 'id', 'harvest_operation_id', 'batch_id');
    }
}
