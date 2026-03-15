<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Observers\PartnerLoanObserver;
use Database\Factories\PartnerLoanFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([PartnerLoanObserver::class])]
class PartnerLoan extends Model
{
    /** @use HasFactory<PartnerLoanFactory> */
    use HasFactory;

    protected $fillable = [
        'loanable_type',
        'loanable_id',
        'date',
        'amount',
        'payment_method',
        'treasury_account_id',
        'journal_entry_id',
        'description',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function loanable(): MorphTo
    {
        return $this->morphTo();
    }

    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'treasury_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(PartnerLoanRepayment::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) max(0, $this->amount - $this->repayments()->sum('amount'));
    }
}
