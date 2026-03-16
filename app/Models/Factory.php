<?php

namespace App\Models;

use App\Enums\FactoryStatementStatus;
use App\Enums\FactoryType;
use App\Observers\FactoryObserver;
use Database\Factories\FactoryFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([FactoryObserver::class])]
class Factory extends Model
{
    /** @use HasFactory<FactoryFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'account_id',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(FactoryStatement::class);
    }

    public function activeStatement(): HasOne
    {
        return $this->hasOne(FactoryStatement::class)->where('status', FactoryStatementStatus::Open)->latest();
    }

    /**
     * Open a new statement session, carrying over any outstanding balance.
     */
    public function openNewStatement(?string $title = null, ?string $notes = null): FactoryStatement
    {
        // Close any existing open statement first
        $this->statements()->where('status', FactoryStatementStatus::Open)->update([
            'status' => FactoryStatementStatus::Closed,
            'closed_at' => now()->toDateString(),
            'closing_balance' => $this->outstanding_balance,
        ]);

        return $this->statements()->create([
            'opened_at' => now()->toDateString(),
            'title' => $title,
            'opening_balance' => $this->outstanding_balance,
            'status' => FactoryStatementStatus::Open,
            'created_by' => auth()->id(),
            'notes' => $notes,
        ]);
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
     * Outstanding balance derived directly from the dedicated GL account.
     * Positive = We owe them (Credit balance). Negative = They owe us / Advance payment (Debit balance).
     */
    public function getOutstandingBalanceAttribute(): float
    {
        if ($this->account_id) {
            // For Liability accounts, balance is usually Credit - Debit
            // Using absolute value for common display, but need to check if we need sign
            return (float) $this->account->balance;
        }

        return 0;
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
        if (! $this->account_id) {
            return [
                'purchases' => 0.0,
                'payments' => 0.0,
            ];
        }

        $startOfYear = now()->startOfYear();

        $purchases = (float) JournalLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('journalEntry', fn ($q) => $q->where('date', '>=', $startOfYear))
            ->sum('credit');

        $payments = (float) JournalLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('journalEntry', fn ($q) => $q->where('date', '>=', $startOfYear))
            ->sum('debit');

        return [
            'purchases' => $purchases,
            'payments' => $payments,
        ];
    }

    /**
     * Calculate purchases and payments for the past year.
     */
    public function getPastYearActivityAttribute(): array
    {
        if (! $this->account_id) {
            return [
                'purchases' => 0.0,
                'payments' => 0.0,
            ];
        }

        $startOfPastYear = now()->subYear()->startOfYear();
        $endOfPastYear = now()->subYear()->endOfYear();

        $purchases = (float) JournalLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$startOfPastYear, $endOfPastYear]))
            ->sum('credit');

        $payments = (float) JournalLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$startOfPastYear, $endOfPastYear]))
            ->sum('debit');

        return [
            'purchases' => $purchases,
            'payments' => $payments,
        ];
    }
}
