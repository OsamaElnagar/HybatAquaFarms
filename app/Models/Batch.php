<?php

namespace App\Models;

use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use App\Observers\BatchObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([BatchObserver::class])]
class Batch extends Model
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory, Cacheable;

    protected $fillable = [
        'batch_code',
        'farm_id',
        'unit_id',
        'species_id',
        'factory_id',
        'entry_date',
        'initial_quantity',
        'current_quantity',
        'initial_weight_avg',
        'current_weight_avg',
        'unit_cost',
        'total_cost',
        'source',
        'status',
        'notes',
        'is_cycle_closed',
        'closure_date',
        'total_feed_cost',
        'total_operating_expenses',
        'total_revenue',
        'net_profit',
        'closed_by',
        'closure_notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'initial_weight_avg' => 'decimal:3',
            'current_weight_avg' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'source' => BatchSource::class,
            'status' => BatchStatus::class,
            'is_cycle_closed' => 'boolean',
            'closure_date' => 'date',
            'total_feed_cost' => 'decimal:2',
            'total_operating_expenses' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'net_profit' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, 'unit_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(BatchMovement::class);
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class);
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'source');
    }

    public function batchPayments(): HasMany
    {
        return $this->hasMany(BatchPayment::class);
    }

    public function harvestOperations(): HasMany
    {
        return $this->hasMany(HarvestOperation::class);
    }

    public function harvests(): HasManyThrough
    {
        return $this->hasManyThrough(Harvest::class, HarvestOperation::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function pettyCashTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }

    /**
     * Calculate total amount paid for this batch.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->batchPayments()->sum('amount');
    }

    /**
     * Calculate outstanding balance for this batch.
     * Total cost minus total payments.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $totalCost = (float) ($this->total_cost ?? 0);
        $totalPaid = $this->total_paid;

        return max(0, $totalCost - $totalPaid);
    }

    /**
     * Check if batch is fully paid.
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->outstanding_balance <= 0 && $this->total_cost > 0;
    }

    /**
     * Get payment status badge color.
     */
    public function getPaymentStatusAttribute(): string
    {
        if (! $this->total_cost || $this->total_cost <= 0) {
            return 'gray'; // No cost to pay
        }

        if ($this->is_fully_paid) {
            return 'success'; // Fully paid
        }

        $paidPercentage = ($this->total_paid / $this->total_cost) * 100;

        if ($paidPercentage >= 80) {
            return 'warning'; // Mostly paid
        }

        return 'danger'; // Not paid or partially paid
    }

    /**
     * Calculate total feed consumed by this batch (in kg).
     */
    public function getTotalFeedConsumedAttribute(): float
    {
        return (float) $this->dailyFeedIssues()->sum('quantity');
    }

    /**
     * Calculate total feed cost for this batch.
     * Uses average cost from feed stocks at the time of issue.
     */
    public function getTotalFeedCostAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_feed_cost'] !== null
        ) {
            return (float) $this->attributes['total_feed_cost'];
        }

        $totalCost = 0;

        foreach ($this->dailyFeedIssues as $issue) {
            // Try to get average cost from feed stock
            $feedStock = FeedStock::where('feed_item_id', $issue->feed_item_id)
                ->where('feed_warehouse_id', $issue->feed_warehouse_id)
                ->first();

            $costPerUnit =
                $feedStock?->average_cost ??
                ($issue->feedItem?->standard_cost ?? 0);
            $totalCost += $issue->quantity * $costPerUnit;
        }

        return (float) $totalCost;
    }

    /**
     * Calculate allocated operating expenses for this batch.
     * Based on batch-specific vouchers and petty cash transactions.
     */
    public function getAllocatedExpensesAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_operating_expenses'] !== null
        ) {
            return (float) $this->attributes['total_operating_expenses'];
        }

        // Sum batch-specific vouchers
        $voucherTotal = (float) $this->vouchers()->sum('amount');

        // Sum batch-specific petty cash transactions (expenses only)
        $pettyCashTotal = (float) $this->pettyCashTransactions()
            ->where('type', 'expense')
            ->sum('amount');

        return $voucherTotal + $pettyCashTotal;
    }

    /**
     * Calculate total cycle expenses (hatchery cost + feed + operating expenses).
     */
    public function getTotalCycleExpensesAttribute(): float
    {
        $hatcheryCost = (float) ($this->total_cost ?? 0);
        $feedCost = $this->total_feed_cost;
        $operatingExpenses = $this->allocated_expenses;

        return $hatcheryCost + $feedCost + $operatingExpenses;
    }

    /**
     * Calculate total revenue from harvests/sales.
     * UPDATED: Now uses order_items via orders/sales_orders
     */
    public function getTotalRevenueAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_revenue'] !== null
        ) {
            return (float) $this->attributes['total_revenue'];
        }

        // Sum subtotal of all OrderItems belonging to this batch that are part of a SalesOrder
        return (float) \App\Models\OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->has('salesOrders') // Must be sold
                    ->whereHas('harvestOperation', function ($hop) {
                        $hop->where('batch_id', $this->id);
                    });
            })
            ->sum('subtotal');
    }

    /**
     * Calculate net profit (revenue - total expenses).
     */
    public function getNetProfitAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['net_profit'] !== null
        ) {
            return (float) $this->attributes['net_profit'];
        }

        return $this->total_revenue - $this->total_cycle_expenses;
    }

    /**
     * Calculate profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_revenue <= 0) {
            return 0;
        }

        return ($this->net_profit / $this->total_revenue) * 100;
    }

    /**
     * Check if cycle is closed.
     */
    public function getIsClosedAttribute(): bool
    {
        return (bool) $this->is_cycle_closed;
    }

    /**
     * Get days since batch entry.
     */
    public function getDaysSinceEntryAttribute(): int
    {
        if (! $this->entry_date) {
            return 0;
        }

        return now()->diffInDays($this->entry_date);
    }
}
