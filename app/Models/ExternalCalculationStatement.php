<?php

namespace App\Models;

use App\Enums\ExternalCalculationStatementStatus;
use App\Enums\ExternalCalculationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalCalculationStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_calculation_id',
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
            'status' => ExternalCalculationStatementStatus::class,
        ];
    }

    public function externalCalculation(): BelongsTo
    {
        return $this->belongsTo(ExternalCalculation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ExternalCalculationEntry::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Total debits (Payments, money out) within this session.
     */
    public function getTotalDebitsAttribute(): float
    {
        return (float) $this->entries()
            ->where('type', ExternalCalculationType::Payment)
            ->sum('amount');
    }

    /**
     * Total credits (Receipts, money in) within this session.
     */
    public function getTotalCreditsAttribute(): float
    {
        return (float) $this->entries()
            ->where('type', ExternalCalculationType::Receipt)
            ->sum('amount');
    }

    /**
     * Net balance for this session = opening + credits - debits.
     * Assuming Receipts increase balance (money in) and Payments decrease it.
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
        return $query->where('status', ExternalCalculationStatementStatus::Open);
    }
}
