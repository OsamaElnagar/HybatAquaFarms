<?php

namespace App\Models;

use App\Enums\FarmStatus;
use App\Observers\FarmObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy([FarmObserver::class])]
class Farm extends Model
{
    /** @use HasFactory<\Database\Factories\FarmFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'size',
        'location',
        'status',
        'established_date',
        'manager_id',
        'notes',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    protected function casts(): array
    {
        return [
            'status' => FarmStatus::class,
            'established_date' => 'date',
            'size' => 'decimal:2',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(FarmUnit::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function pettyCashes(): BelongsToMany
    {
        return $this->belongsToMany(PettyCash::class, 'farm_petty_cash');
    }

    public function pettyCashTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function externalCalculations(): HasMany
    {
        return $this->hasMany(ExternalCalculation::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function feedWarehouses(): HasMany
    {
        return $this->hasMany(FeedWarehouse::class);
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class);
    }

    /**
     * Get total feed consumed by this farm in a date range.
     */
    public function getTotalFeedConsumed(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->dailyFeedIssues();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return (float) $query->sum('quantity');
    }

    /**
     * Get active batches count.
     */
    public function getActiveBatchesCountAttribute(): int
    {
        return $this->attributes['active_batches_count'] ?? $this->batches()->where('status', 'active')->count();
    }

    /**
     * Get total current stock quantity.
     */
    public function getTotalCurrentStockAttribute(): int
    {
        return (int) ($this->attributes['total_current_stock'] ?? $this->batches()->where('status', 'active')->sum('current_quantity'));
    }

    #[Scope]
    public function active($query)
    {
        $query->where('status', FarmStatus::Active);
    }
}
