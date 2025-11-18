<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesItem extends Model
{
    /** @use HasFactory<\Database\Factories\SalesItemFactory> */
    use HasFactory;

    protected $fillable = [
        "sales_order_id",
        "batch_id",
        "species_id",
        "item_name",
        "description",
        "quantity",
        "weight_kg",
        "average_fish_weight",
        "grade",
        "size_category",
        "unit_price",
        "pricing_unit",
        "discount_percent",
        "discount_amount",
        "subtotal",
        "total_price",
        "fulfilled_quantity",
        "fulfilled_weight",
        "fulfillment_status",
        "line_number",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "quantity" => "decimal:2",
            "weight_kg" => "decimal:3",
            "average_fish_weight" => "decimal:3",
            "unit_price" => "decimal:2",
            "discount_percent" => "decimal:2",
            "discount_amount" => "decimal:2",
            "subtotal" => "decimal:2",
            "total_price" => "decimal:2",
            "fulfilled_quantity" => "decimal:2",
            "fulfilled_weight" => "decimal:3",
        ];
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->calculatePricing();
        });

        static::updating(function ($item) {
            if (
                $item->isDirty([
                    "quantity",
                    "weight_kg",
                    "unit_price",
                    "discount_percent",
                    "pricing_unit",
                ])
            ) {
                $item->calculatePricing();
            }
        });
    }

    /**
     * Calculate pricing based on unit price and quantity/weight
     */
    public function calculatePricing(): void
    {
        // Calculate subtotal based on pricing unit
        if ($this->pricing_unit === "piece" && $this->quantity) {
            $this->subtotal = $this->quantity * $this->unit_price;
        } else {
            $this->subtotal = $this->weight_kg * $this->unit_price;
        }

        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount_amount =
                $this->subtotal * ($this->discount_percent / 100);
        }

        // Calculate total
        $this->total_price = $this->subtotal - $this->discount_amount;

        // Calculate average fish weight if both quantity and weight are provided
        if ($this->quantity > 0 && $this->weight_kg > 0) {
            $this->average_fish_weight =
                ($this->weight_kg * 1000) / $this->quantity;
        }
    }

    /**
     * Get fulfillment progress percentage
     */
    public function getFulfillmentProgressAttribute(): float
    {
        if ($this->pricing_unit === "piece" && $this->quantity > 0) {
            return ($this->fulfilled_quantity / $this->quantity) * 100;
        } elseif ($this->weight_kg > 0) {
            return ($this->fulfilled_weight / $this->weight_kg) * 100;
        }

        return 0;
    }

    /**
     * Get remaining quantity to fulfill
     */
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - $this->fulfilled_quantity;
    }

    /**
     * Get remaining weight to fulfill
     */
    public function getRemainingWeightAttribute(): float
    {
        return $this->weight_kg - $this->fulfilled_weight;
    }

    /**
     * Check if item is fully fulfilled
     */
    public function getIsFullyFulfilledAttribute(): bool
    {
        return $this->fulfillment_status === "fulfilled";
    }

    /**
     * Get item display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->item_name) {
            return $this->item_name;
        }

        $name = $this->species->name ?? "صنف";

        if ($this->size_category) {
            $name .= " - " . $this->size_category;
        }

        if ($this->grade) {
            $name .= " (درجة " . $this->grade . ")";
        }

        return $name;
    }

    // Relationships
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    /**
     * Get harvests that fulfilled this sales item
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(
            Harvest::class,
            "sales_order_id",
            "sales_order_id",
        )->where("batch_id", $this->batch_id);
    }
}
