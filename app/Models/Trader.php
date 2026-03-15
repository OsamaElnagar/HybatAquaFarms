<?php

namespace App\Models;

use App\Enums\TraderStatementStatus;
use App\Observers\TraderObserver;
use Database\Factories\TraderFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([TraderObserver::class])]
class Trader extends Model
{
    /** @use HasFactory<TraderFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'phone2',
        'email',
        'address',
        'trader_type',
        'payment_terms_days',
        'credit_limit',
        'commission_rate',
        'commission_type',
        'default_transport_cost_per_kg',
        'default_transport_cost_flat',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'default_transport_cost_per_kg' => 'decimal:2',
            'default_transport_cost_flat' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'counterparty');
    }

    public function clearingEntries(): HasMany
    {
        return $this->hasMany(ClearingEntry::class);
    }

    public function partnerLoans(): MorphMany
    {
        return $this->morphMany(PartnerLoan::class, 'loanable');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(TraderStatement::class);
    }

    public function activeStatement(): HasOne
    {
        return $this->hasOne(TraderStatement::class)->where('status', TraderStatementStatus::Open)->latest();
    }

    /**
     * Open a new statement session, carrying over any outstanding balance.
     */
    public function openNewStatement(?string $title = null, ?string $notes = null, array $harvestOperationIds = []): TraderStatement
    {
        // Close any existing open statement first
        $this->statements()->where('status', TraderStatementStatus::Open)->update([
            'status' => TraderStatementStatus::Closed,
            'closed_at' => now()->toDateString(),
            'closing_balance' => $this->outstanding_balance,
        ]);

        $statement = $this->statements()->create([
            'opened_at' => now()->toDateString(),
            'title' => $title,
            'opening_balance' => $this->outstanding_balance,
            'status' => TraderStatementStatus::Open,
            'created_by' => auth()->id(),
            'notes' => $notes,
        ]);

        if (! empty($harvestOperationIds)) {
            $statement->harvestOperations()->sync($harvestOperationIds);
        }

        return $statement;
    }

    /**
     * Total outstanding loan balance (what the farm owes this trader).
     */
    public function getPartnerLoansBalanceAttribute(): float
    {
        return (float) $this->partnerLoans
            ->sum(fn (PartnerLoan $loan) => $loan->remaining_balance);
    }

    /**
     * Outstanding balance derived directly from the dedicated GL account.
     * Positive = They owe us (Debit balance). Negative = We owe them / Advance payment (Credit balance).
     */
    public function getOutstandingBalanceAttribute(): float
    {
        if ($this->account_id) {
            return (float) $this->account->balance;
        }

        return 0;
    }
}
