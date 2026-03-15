<?php

namespace App\Models;

use App\Enums\TraderStatementStatus;
use Database\Factories\TraderStatementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TraderStatement extends Model
{
    /** @use HasFactory<TraderStatementFactory> */
    use HasFactory;

    protected $fillable = [
        'trader_id',
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
            'status' => TraderStatementStatus::class,
        ];
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(Trader::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function harvestOperations(): BelongsToMany
    {
        return $this->belongsToMany(HarvestOperation::class, 'harvest_operation_trader_statement');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Total debits (sales, money-out) within this session.
     */
    public function getTotalDebitsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('trader_statement_id', $this->id))
            ->where('account_id', $this->trader->account_id)
            ->sum('debit');
    }

    /**
     * Total credits (payments received, money-in) within this session.
     */
    public function getTotalCreditsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('trader_statement_id', $this->id))
            ->where('account_id', $this->trader->account_id)
            ->sum('credit');
    }

    /**
     * Net balance for this session = opening + debits - credits.
     */
    public function getNetBalanceAttribute(): float
    {
        return (float) $this->opening_balance + $this->total_debits - $this->total_credits;
    }

    /**
     * Scope to only active (open) statements.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', TraderStatementStatus::Open);
    }
}
