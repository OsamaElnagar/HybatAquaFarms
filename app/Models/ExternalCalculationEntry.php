<?php

namespace App\Models;

use App\Enums\ExternalCalculationType;
use App\Observers\ExternalCalculationEntryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ExternalCalculationEntryObserver::class])]
class ExternalCalculationEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_calculation_id',
        'farm_id',
        'treasury_account_id',
        'account_id',
        'type',
        'amount',
        'date',
        'reference_number',
        'description',
        'created_by',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'type' => ExternalCalculationType::class,
        ];
    }

    public function externalCalculation(): BelongsTo
    {
        return $this->belongsTo(ExternalCalculation::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
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
}
