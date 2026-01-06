<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, Cacheable;

    protected $table = 'orders_items';

    protected $fillable = [
        'order_id',
        'box_id',
        'quantity',
        'weight_per_box',
        'total_weight',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight_per_box' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot the model and attach event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate total_weight and subtotal before saving
        static::saving(function ($item) {
            if ($item->quantity && $item->weight_per_box) {
                $item->total_weight = $item->quantity * $item->weight_per_box;
            }

            // Default calculation: per kg if total_weight exists, else per unit?
            // Let's assume per kg if weight exists, otherwise per box.
            // But simple logic for now: unit_price is usually per KG in this domain (AquaFarms).
            if ($item->unit_price) {
                $item->subtotal = ($item->total_weight ?? 0) * $item->unit_price;
            }
        });

        static::saved(function ($item) {
            $item->order->touch(); // Touch parent order to maybe trigger its recalculation if one existed
        });
    }

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }
}
