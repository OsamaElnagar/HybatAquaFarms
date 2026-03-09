<?php

namespace App\Models;

use App\Enums\FactoryType;
use App\Observers\FactoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function batchFish(): HasMany
    {
        return $this->hasMany(BatchFish::class);
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

    public function partnerLoans(): MorphMany
    {
        return $this->morphMany(PartnerLoan::class, 'loanable');
    }

    /**
     * Total outstanding loan balance (what the farm owes this factory as loans).
     */
    public function getPartnerLoansBalanceAttribute(): float
    {
        return (float) $this->partnerLoans
            ->sum(fn (PartnerLoan $loan) => $loan->remaining_balance);
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

        // Seed purchases (batch fish)
        $totalSeedPurchases = $this->batchFish()
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

    /**
     * Calculate purchases and payments for the current year.
     */
    public function getCurrentYearActivityAttribute(): array
    {
        $startOfYear = now()->startOfYear();
        $purchases = 0.0;
        $payments = 0.0;

        if ($this->type === FactoryType::FEEDS) {
            $purchases = $this->feedMovements()
                ->where('movement_type', 'in')
                ->where('date', '>=', $startOfYear)
                ->get()
                ->sum(function ($movement) {
                    $unitCost = $movement->feedItem?->standard_cost ?? 0;

                    return (float) ($movement->total_cost ?? ($movement->quantity * $unitCost));
                });

            $payments = $this->factoryPayments()
                ->where('date', '>=', $startOfYear)
                ->sum('amount');

            $voucherPayments = $this->vouchers()
                ->where('voucher_type', 'payment')
                ->where('date', '>=', $startOfYear)
                ->sum('amount');

            $payments += $voucherPayments;
        } elseif ($this->type === FactoryType::SEEDS) {
            $purchases = $this->batchFish()
                ->whereHas('batch', function ($q) use ($startOfYear) {
                    $q->where('entry_date', '>=', $startOfYear);
                })
                ->sum('total_cost');

            $payments = $this->batchPayments()
                ->where('date', '>=', $startOfYear)
                ->sum('amount');
        }

        return [
            'purchases' => (float) $purchases,
            'payments' => (float) $payments,
        ];
    }

    /**
     * Calculate purchases and payments for the past year.
     */
    public function getPastYearActivityAttribute(): array
    {
        $startOfPastYear = now()->subYear()->startOfYear();
        $endOfPastYear = now()->subYear()->endOfYear();

        $purchases = 0.0;
        $payments = 0.0;

        if ($this->type === FactoryType::FEEDS) {
            $purchases = $this->feedMovements()
                ->where('movement_type', 'in')
                ->whereBetween('date', [$startOfPastYear, $endOfPastYear])
                ->get()
                ->sum(function ($movement) {
                    $unitCost = $movement->feedItem?->standard_cost ?? 0;

                    return (float) ($movement->total_cost ?? ($movement->quantity * $unitCost));
                });

            $payments = $this->factoryPayments()
                ->whereBetween('date', [$startOfPastYear, $endOfPastYear])
                ->sum('amount');

            $voucherPayments = $this->vouchers()
                ->where('voucher_type', 'payment')
                ->whereBetween('date', [$startOfPastYear, $endOfPastYear])
                ->sum('amount');

            $payments += $voucherPayments;
        } elseif ($this->type === FactoryType::SEEDS) {
            $purchases = $this->batchFish()
                ->whereHas('batch', function ($q) use ($startOfPastYear, $endOfPastYear) {
                    $q->whereBetween('entry_date', [$startOfPastYear, $endOfPastYear]);
                })
                ->sum('total_cost');

            $payments = $this->batchPayments()
                ->whereBetween('date', [$startOfPastYear, $endOfPastYear])
                ->sum('amount');
        }

        return [
            'purchases' => (float) $purchases,
            'payments' => (float) $payments,
        ];
    }
}
