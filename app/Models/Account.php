<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'is_cash',
        'code',
        'name',
        'type',
        'farm_id',
        'parent_id',
        'is_active',
        'description',
        'is_cash',
    ];

    protected function casts(): array
    {
        return [
            'is_cash' => 'boolean',
            'type' => AccountType::class,
            'is_active' => 'boolean',
            'is_cash' => 'boolean',
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

    public function getBalanceAttribute(): float
    {
        $debits = $this->journalLines()->sum('debit');
        $credits = $this->journalLines()->sum('credit');

        // For asset and expense accounts, debit increases balance
        // For liability, equity, and income accounts, credit increases balance
        if (in_array($this->type, [AccountType::Asset, AccountType::Expense])) {
            return $debits - $credits;
        }

        return $credits - $debits;
    }

    public function scopeCash($query)
    {
        return $query->where('is_cash', true)
                     ->orWhereRaw("code LIKE '111%' OR code = '1120'");
    }

    public function getTreasuryCashBalanceAttribute(): float
    {
        return self::cash()->sum('balance');
    }
}
