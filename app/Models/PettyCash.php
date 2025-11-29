<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PettyCash extends Model
{
    /** @use HasFactory<\Database\Factories\PettyCashFactory> */
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'custodian_employee_id',
        'account_id',
        'opening_balance',
        'opening_date',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'custodian_employee_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getCurrentBalanceAttribute(): float
    {
        $totalIn = $this->transactions()->where('direction', 'in')->sum('amount');
        $totalOut = $this->transactions()->where('direction', 'out')->sum('amount');

        $balance = (float) $this->opening_balance + $totalIn - $totalOut;

        // If linked to account, add account balance (accounting postings)
        if ($this->account) {
            $balance += $this->account->balance;
        }

        return $balance;
    }
}
