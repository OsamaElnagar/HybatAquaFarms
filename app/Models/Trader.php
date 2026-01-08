<?php

namespace App\Models;

use App\Observers\TraderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([TraderObserver::class])]
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
        $totalCreditSales = array_key_exists('pending_sales_total', $this->attributes)
            ? (float) $this->attributes['pending_sales_total']
            : (float) $this->salesOrders()
                ->whereIn('payment_status', ['pending', 'partial'])
                ->sum('net_amount');

        $clearingTotal = array_key_exists('clearing_entries_total', $this->attributes)
            ? (float) $this->attributes['clearing_entries_total']
            : (float) $this->clearingEntries()->sum('amount');

        $receiptVouchersTotal = array_key_exists('receipt_vouchers_total', $this->attributes)
            ? (float) $this->attributes['receipt_vouchers_total']
            : (float) $this->vouchers()
                ->where('voucher_type', \App\Enums\VoucherType::Receipt)
                ->sum('amount');

        $totalSettled = $clearingTotal + $receiptVouchersTotal;

        return (float) max(0, $totalCreditSales - $totalSettled);
    }
}
