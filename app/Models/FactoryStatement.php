<?php

namespace App\Models;

use App\Enums\FactoryStatementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'factory_id',
        'title',
        'created_by',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'status' => FactoryStatementStatus::class,
        ];
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Total debits (payments, settlements) within this session.
     */
    public function getTotalDebitsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('factory_statement_id', $this->id))
            ->where('account_id', $this->factory->account_id)
            ->sum('debit');
    }

    /**
     * Total credits (purchases, money-in) within this session.
     */
    public function getTotalCreditsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('factory_statement_id', $this->id))
            ->where('account_id', $this->factory->account_id)
            ->sum('credit');
    }

    /**
     * Net balance for this session = opening + credits - debits.
     * For Factories (Liabilities), Credit increases balance (purchase), Debit decreases it (payment).
     */
    public function getNetBalanceAttribute(): float
    {
        return (float) $this->opening_balance + $this->total_credits - $this->total_debits;
    }

    /**
     * Scope to only active (open) statements.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', FactoryStatementStatus::Open);
    }
}
