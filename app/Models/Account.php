<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([AccountObserver::class])]
class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'current_balance',
        'farm_id',
        'parent_id',
        'is_active',
        'is_treasury',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'is_active' => 'boolean',
            'is_treasury' => 'boolean',
            'current_balance' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Calculate account balance from journal lines
     * Balance = Total Debits - Total Credits
     */
    public function getBalanceAttribute(): float
    {
        $debits = (float) $this->journalLines()->sum('debit');
        $credits = (float) $this->journalLines()->sum('credit');

        return $debits - $credits;
    }

    /**
     * Get balance as of a specific date
     */
    public function getBalanceAsOf(string $date): float
    {
        $debits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $date))
            ->sum('debit');

        $credits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $date))
            ->sum('credit');

        return $debits - $credits;
    }

    /**
     * Scope for treasury accounts only
     */
    public function scopeTreasury($query)
    {
        return $query->where('is_treasury', true);
    }
}
