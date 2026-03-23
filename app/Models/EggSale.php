<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EggSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'egg_collection_id',
        'batch_id',
        'trader_id',
        'is_cash_sale',
        'sale_date',
        'trays_sold',
        'eggs_per_tray',
        'total_eggs',
        'unit_price',
        'subtotal',
        'transport_cost',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'is_cash_sale' => 'boolean',
            'trays_sold' => 'integer',
            'eggs_per_tray' => 'integer',
            'total_eggs' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'transport_cost' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->sale_number) {
                $model->sale_number = static::generateSaleNumber();
            }

            // Calculate total eggs
            if ($model->trays_sold && $model->eggs_per_tray) {
                $model->total_eggs = $model->trays_sold * $model->eggs_per_tray;
            }

            // Calculate subtotal
            $model->subtotal = ($model->trays_sold ?? 0) * ($model->unit_price ?? 0);

            // Calculate net amount
            $model->net_amount = $model->subtotal
                + ($model->transport_cost ?? 0)
                + ($model->tax_amount ?? 0)
                - ($model->discount_amount ?? 0);

            // Auto-set batch_id from egg_collection
            if (! $model->batch_id && $model->egg_collection_id) {
                $collection = $model->eggCollection;
                if ($collection) {
                    $model->batch_id = $collection->batch_id;
                }
            }
        });
    }

    public static function generateSaleNumber(): string
    {
        $lastSale = static::latest('id')->first();
        $number = $lastSale ? ((int) substr($lastSale->sale_number, 3)) + 1 : 1;

        // Keep incrementing until we find an unused number
        do {
            $candidate = 'EGS-'.str_pad($number, 5, '0', STR_PAD_LEFT);
            $exists = static::where('sale_number', $candidate)->exists();
            $number++;
        } while ($exists);

        return $candidate;
    }

    public function eggCollection(): BelongsTo
    {
        return $this->belongsTo(EggCollection::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

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
}
