<?php

namespace App\Models;

use App\Enums\EmployeeStatementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
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
            'status' => EmployeeStatementStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
     * Total debits (amounts due from employee, like advances) within this session.
     */
    public function getTotalDebitsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('employee_statement_id', $this->id))
            // This is a bit tricky since we don't have individual account_ids.
            // But we can filter by the journal entries linked to this statement.
            ->sum('debit');
    }

    /**
     * Total credits (amounts paid by employee or salary) within this session.
     */
    public function getTotalCreditsAttribute(): float
    {
        return (float) JournalLine::query()
            ->whereHas('journalEntry', fn ($q) => $q->where('employee_statement_id', $this->id))
            ->sum('credit');
    }

    /**
     * Net balance for this session = opening + debits - credits.
     * Note: For employees, more debits means they owe us more.
     */
    public function getNetBalanceAttribute(): float
    {
        return (float) $this->opening_balance + $this->total_debits - $this->total_credits;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', EmployeeStatementStatus::Open);
    }
}
