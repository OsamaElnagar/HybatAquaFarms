<?php

namespace App\Models;

use App\Observers\FactoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([FactoryObserver::class])]
class Factory extends Model
{
    /** @use HasFactory<\Database\Factories\FactoryFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
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
}
