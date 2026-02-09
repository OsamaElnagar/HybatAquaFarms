<?php

namespace App\Models;

use App\Enums\FactoryType;
use App\Observers\FactoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FactoryObserver::class])]
class Factory extends Model
{
    /** @use HasFactory<\Database\Factories\FactoryFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'supplier_activity_id',
        'contact_person',
        'phone',
        'phone2',
        'email',
        'address',
        'payment_terms_days',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => FactoryType::class,
        ];
    }

    public function feedMovements(): HasMany
    {
        return $this->hasMany(FeedMovement::class);
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'counterparty');
    }

    public function clearingEntries(): HasMany
    {
        return $this->hasMany(ClearingEntry::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function factoryPayments(): HasMany
    {
        return $this->hasMany(FactoryPayment::class);
    }

    public function batchPayments(): HasMany
    {
        return $this->hasMany(BatchPayment::class);
    }

    public function feedItems(): HasMany
    {
        return $this->hasMany(FeedItem::class);
    }

    public function supplierActivity(): BelongsTo
    {
        return $this->belongsTo(SupplierActivity::class);
    }

    /**
     * Calculate outstanding payable balance for this factory.
     * Total feed purchases + seed purchases minus payments and settlements.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        // Feed purchases - calculate from feed movements using standard_cost from FeedItem
        $totalFeedPurchases = $this->feedMovements()
            ->where('movement_type', 'in')
            ->get()
            ->sum(function ($movement) {
                $feedItem = $movement->feedItem;
                $unitCost = $feedItem?->standard_cost ?? 0;

                return (float) $movement->quantity * $unitCost;
            });

        // Seed purchases (batches)
        $totalSeedPurchases = $this->batches()
            ->whereNotNull('total_cost')
            ->sum('total_cost');

        $totalPurchases = $totalFeedPurchases + $totalSeedPurchases;

        // Old voucher payments (keeping for backward compatibility)
        $totalPaidVouchers = $this->vouchers()
            ->where('voucher_type', 'payment')
            ->sum('amount');

        // Factory payments (for feed purchases)
        $totalPaidFactoryPayments = $this->factoryPayments()->sum('amount');

        // Batch payments (for seed purchases)
        $totalPaidBatchPayments = $this->batchPayments()->sum('amount');

        $totalSettled = $this->clearingEntries()->sum('amount');

        return (float) max(0, $totalPurchases - $totalPaidVouchers - $totalPaidFactoryPayments - $totalPaidBatchPayments - $totalSettled);
    }

    #[Scope]
    public function feedFactory($query)
    {
        return $query->where('type', FactoryType::FEEDS);
    }

    #[Scope]
    public function seedFactory($query)
    {
        return $query->where('type', FactoryType::SEEDS);
    }
}
