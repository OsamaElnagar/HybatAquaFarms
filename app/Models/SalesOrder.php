<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Observers\SalesOrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([SalesOrderObserver::class])]
class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'harvest_operation_id',
        'trader_id',
        'date',
        'boxes_subtotal',
        'commission_rate',
        'commission_amount',
        'transport_cost',
        'tax_amount',
        'discount_amount',
        'total_before_commission',
        'net_amount',
        'payment_status',
        'delivery_status',
        'delivery_date',
        'delivery_address',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'delivery_date' => 'date',
            'boxes_subtotal' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'transport_cost' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_before_commission' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'delivery_status' => DeliveryStatus::class,
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Auto-generate order number
            if (! $model->order_number) {
                $model->order_number = static::generateOrderNumber();
            }

            // Copy commission rate from trader if not set
            if ($model->trader && ! $model->commission_rate) {
                $model->commission_rate = $model->trader->commission_rate ?? 0;
            }
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $lastOrder = static::latest('id')->first();
        $number = $lastOrder ? ((int) substr($lastOrder->order_number, 3)) + 1 : 1;

        return 'SO-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate all totals from linked orders
     */
    public function recalculateTotals(): void
    {
        // Sum all orders items
        $this->boxes_subtotal = $this->orders->map(fn ($order) => $order->items->sum('subtotal'))->sum();

        // Calculate commission
        if ($this->commission_rate) {
            $this->commission_amount = ($this->boxes_subtotal * $this->commission_rate) / 100;
        } else {
            $this->commission_amount = 0;
        }

        // Total before commission
        $this->total_before_commission =
            $this->boxes_subtotal +
            ($this->transport_cost ?? 0) +
            ($this->tax_amount ?? 0) -
            ($this->discount_amount ?? 0);

        // Net amount (after commission)
        $this->net_amount = $this->total_before_commission - $this->commission_amount;

        $this->saveQuietly(); // Don't trigger observers
    }

    // Relationships
    public function harvestOperation(): BelongsTo
    {
        return $this->belongsTo(HarvestOperation::class);
    }

    public function farm(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Farm::class, HarvestOperation::class, 'id', 'id', 'harvest_operation_id', 'farm_id');
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(Trader::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'source');
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function harvests()
    {
        return Harvest::whereIn('id', function ($query) {
            $query->select('orders.harvest_id')
                ->from('orders')
                ->join('order_sales_order', 'orders.id', '=', 'order_sales_order.order_id')
                ->where('order_sales_order.sales_order_id', $this->id);
        });
    }

    public function harvestOperations()
    {
        return HarvestOperation::whereIn('id', function ($query) {
            $query->select('orders.harvest_operation_id')
                ->from('orders')
                ->join('order_sales_order', 'orders.id', '=', 'order_sales_order.order_id')
                ->where('order_sales_order.sales_order_id', $this->id);
        });
    }

    // Calculated Attributes
    public function getTotalBoxesAttribute(): int
    {
        return (int) $this->orders->map(fn ($order) => $order->items->sum('quantity'))->sum();
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->orders->map(fn ($order) => $order->items->sum('total_weight'))->sum();
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->total_boxes;
    }

    // Legacy compatibility - map old total_amount to net_amount
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->net_amount;
    }
}
