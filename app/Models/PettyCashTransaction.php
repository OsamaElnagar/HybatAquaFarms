<?php

namespace App\Models;

use App\Enums\PettyTransacionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\PettyCashTransactionFactory> */
    use HasFactory, Cacheable;

    protected $fillable = [
        'petty_cash_id',
        'batch_id',
        'voucher_id',
        'expense_category_id',
        'date',
        'direction',
        'amount',
        'description',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'direction' => PettyTransacionType::class,
        ];
    }

    public function pettyCash(): BelongsTo
    {
        return $this->belongsTo(PettyCash::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
