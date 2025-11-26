<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Trader extends Model
{
    /** @use HasFactory<\Database\Factories\TraderFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'phone2',
        'email',
        'address',
        'trader_type',
        'payment_terms_days',
        'credit_limit',
        'commission_rate',
        'commission_type',
        'default_transport_cost_per_kg',
        'default_transport_cost_flat',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'default_transport_cost_per_kg' => 'decimal:2',
            'default_transport_cost_flat' => 'decimal:2',
        ];
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'counterparty');
    }

    public function clearingEntries(): HasMany
    {
        return $this->hasMany(ClearingEntry::class);
    }

    /**
     * Calculate outstanding receivable balance for this trader.
     * Total credit sales (unpaid) minus settlements.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $totalCreditSales = $this->salesOrders()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum('net_amount');

        $totalSettled = $this->clearingEntries()->sum('amount');

        return (float) max(0, $totalCreditSales - $totalSettled);
    }
}
