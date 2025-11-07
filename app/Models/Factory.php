<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Factory extends Model
{
    /** @use HasFactory<\Database\Factories\FactoryFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'phone2',
        'email',
        'address',
        'payment_terms_days',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function feedMovements(): HasMany
    {
        return $this->hasMany(FeedMovement::class);
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
     * Calculate outstanding payable balance for this factory.
     * Total feed purchases minus payments and settlements.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $totalPurchases = $this->feedMovements()
            ->where('movement_type', 'in')
            ->sum('total_cost');

        $totalPaid = $this->vouchers()
            ->where('voucher_type', 'payment')
            ->sum('amount');

        $totalSettled = $this->clearingEntries()->sum('amount');

        return (float) max(0, $totalPurchases - $totalPaid - $totalSettled);
    }
}
