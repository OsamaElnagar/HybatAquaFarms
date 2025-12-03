<?php

namespace App\Models;

use App\Enums\FarmStatus;
use App\Observers\FarmObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function pettyCash(): HasMany
    {
        return $this->hasMany(PettyCash::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
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
        return $this->batches()->where('status', 'active')->count();
    }

    /**
     * Get total current stock quantity.
     */
    public function getTotalCurrentStockAttribute(): int
    {
        return (int) $this->batches()->where('status', 'active')->sum('current_quantity');
    }
}
