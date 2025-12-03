<?php

namespace App\Models;

use App\Enums\PricingUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'harvest_id',
        'harvest_operation_id',
        'batch_id',
        'species_id',
        'box_number',
        'classification',
        'grade',
        'size_category',
        'weight',
        'fish_count',
        'average_fish_weight',
        'trader_id',
        'sales_order_id',
        'unit_price',
        'pricing_unit',
        'subtotal',
        'is_sold',
        'sold_at',
        'line_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:3',
            'average_fish_weight' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'is_sold' => 'boolean',
            'sold_at' => 'datetime',
            'pricing_unit' => PricingUnit::class,
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Auto-calculate average fish weight
            if ($model->fish_count && $model->weight) {
                $model->average_fish_weight = ($model->weight * 1000) / $model->fish_count;
            }
        });

        static::updating(function ($model) {
            // Recalculate average if weight or count changes
            if ($model->isDirty(['fish_count', 'weight']) && $model->fish_count && $model->weight) {
                $model->average_fish_weight = ($model->weight * 1000) / $model->fish_count;
            }

            // Calculate subtotal when selling
            if ($model->isDirty(['unit_price', 'weight', 'fish_count', 'pricing_unit'])) {
                $model->calculateSubtotal();
            }

            // Track sold timestamp
            if ($model->isDirty('is_sold') && $model->is_sold && ! $model->sold_at) {
                $model->sold_at = now();
            }
        });

        static::saved(function ($model) {
            // Update parent sales order totals if this box is linked
            if ($model->sales_order_id) {
                $model->salesOrder->recalculateTotals();
            }
        });
    }

    /**
     * Calculate subtotal based on pricing unit
     */
    public function calculateSubtotal(): void
    {
        if (! $this->unit_price) {
            $this->subtotal = null;

            return;
        }

        $this->subtotal = match ($this->pricing_unit) {
            PricingUnit::Kilogram => $this->weight * $this->unit_price,
            PricingUnit::Piece => $this->fish_count * $this->unit_price,
            PricingUnit::Box => $this->unit_price,
            default => $this->weight * $this->unit_price,
        };
    }

    // Relationships
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function harvestOperation(): BelongsTo
    {
        return $this->belongsTo(HarvestOperation::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(Trader::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    // Helper Methods
    public function assignToSalesOrder(SalesOrder $salesOrder, float $unitPrice, ?PricingUnit $pricingUnit = null): void
    {
        $this->sales_order_id = $salesOrder->id;
        $this->trader_id = $salesOrder->trader_id;
        $this->unit_price = $unitPrice;
        $this->pricing_unit = $pricingUnit ?? PricingUnit::Kilogram;
        $this->is_sold = true;
        $this->calculateSubtotal();
        $this->save();
    }

    public function unassignFromSalesOrder(): void
    {
        $this->sales_order_id = null;
        $this->trader_id = null;
        $this->unit_price = null;
        $this->subtotal = null;
        $this->is_sold = false;
        $this->sold_at = null;
        $this->line_number = null;
        $this->save();
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $parts = [];

        if ($this->classification) {
            $parts[] = $this->classification;
        } elseif ($this->species) {
            $parts[] = $this->species->name;
        }

        if ($this->grade) {
            $parts[] = "درجة {$this->grade}";
        }

        if ($this->size_category) {
            $parts[] = $this->size_category;
        }

        return implode(' - ', $parts) ?: "صندوق #{$this->box_number}";
    }
}
