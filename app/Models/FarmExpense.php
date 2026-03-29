<?php

namespace App\Models;

use App\Enums\FarmExpenseType;
use App\Observers\FarmExpenseObserver;
use Database\Factories\FarmExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FarmExpenseObserver::class])]
class FarmExpense extends Model
{
    /** @use HasFactory<FarmExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'batch_id',
        'expense_category_id',
        'treasury_account_id',
        'account_id',
        'type',
        'amount',
        'date',
        'reference_number',
        'description',
        'created_by',
        'journal_entry_id',
        'advance_repayment_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'type' => FarmExpenseType::class,
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'treasury_account_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function advanceRepayment(): BelongsTo
    {
        return $this->belongsTo(AdvanceRepayment::class);
    }
}
