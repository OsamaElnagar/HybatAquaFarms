<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\RepaymentType;
use App\Observers\PartnerLoanRepaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PartnerLoanRepaymentObserver::class])]
class PartnerLoanRepayment extends Model
{
    /** @use HasFactory<\Database\Factories\PartnerLoanRepaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'partner_loan_id',
        'repayment_type',
        'date',
        'amount',
        'payment_method',
        'treasury_account_id',
        'sales_order_id',
        'journal_entry_id',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'repayment_type' => RepaymentType::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function partnerLoan(): BelongsTo
    {
        return $this->belongsTo(PartnerLoan::class);
    }

    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'treasury_account_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
