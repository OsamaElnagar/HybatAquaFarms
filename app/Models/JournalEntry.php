<?php

namespace App\Models;

use App\Observers\JournalEntryObserver;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([JournalEntryObserver::class])]
class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'date',
        'description',
        'source_type',
        'source_id',
        'is_posted',
        'trader_statement_id',
        'factory_statement_id',
        'external_calculation_statement_id',
        'employee_statement_id',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_posted' => 'boolean',
            'posted_at' => 'datetime',
        ];
    }

    public function traderStatement(): BelongsTo
    {
        return $this->belongsTo(TraderStatement::class);
    }

    public function factoryStatement(): BelongsTo
    {
        return $this->belongsTo(FactoryStatement::class);
    }

    public function externalCalculationStatement(): BelongsTo
    {
        return $this->belongsTo(ExternalCalculationStatement::class);
    }

    public function employeeStatement(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatement::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
