<?php

namespace App\Models;

use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PettyCash extends Model
{
    /** @use HasFactory<\Database\Factories\PettyCashFactory> */
    use Cacheable, HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'custodian_employee_id',
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

    public function getCurrentBalanceAttribute(): float
    {
        $totalIn = array_key_exists('total_in', $this->attributes)
            ? (float) $this->attributes['total_in']
            : (float) $this->transactions()->where('direction', 'in')->sum('amount');

        $totalOut = array_key_exists('total_out', $this->attributes)
            ? (float) $this->attributes['total_out']
            : (float) $this->transactions()->where('direction', 'out')->sum('amount');

        return (float) $this->opening_balance + $totalIn - $totalOut;
    }
}
