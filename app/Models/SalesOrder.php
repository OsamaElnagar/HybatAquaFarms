<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Services\PostingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'farm_id',
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

        static::updating(function ($model) {
            if ($model->isDirty('payment_status') && $model->payment_status === PaymentStatus::Paid) {
                PostingService::post($model, 'sales.payment', $model->net_amount);
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
     * Recalculate all totals from linked harvest boxes
     */
    public function recalculateTotals(): void
    {
        // Sum all boxes
        $this->boxes_subtotal = $this->harvestBoxes()->sum('subtotal') ?? 0;

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
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
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

    public function harvestBoxes(): HasMany
    {
        return $this->hasMany(HarvestBox::class);
    }

    public function harvests(): HasManyThrough
    {
        return $this->hasManyThrough(
            Harvest::class,
            HarvestBox::class,
            'sales_order_id',
            'id',
            'id',
            'harvest_id'
        )->distinct();
    }

    public function harvestOperations(): HasManyThrough
    {
        return $this->hasManyThrough(
            HarvestOperation::class,
            HarvestBox::class,
            'sales_order_id',
            'id',
            'id',
            'harvest_operation_id'
        )->distinct();
    }

    // Calculated Attributes
    public function getTotalBoxesAttribute(): int
    {
        return $this->harvestBoxes()->count();
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->harvestBoxes()->sum('weight');
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->harvestBoxes()->sum('fish_count');
    }

    // Legacy compatibility - map old total_amount to net_amount
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->net_amount;
    }
}
