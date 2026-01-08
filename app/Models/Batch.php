<?php

namespace App\Models;

use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use App\Observers\BatchObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([BatchObserver::class])]
class Batch extends Model
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory;

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

    protected ?float $cachedTotalFeedCost = null;

    protected ?float $cachedAllocatedExpenses = null;

    protected ?float $cachedTotalCycleExpenses = null;

    protected ?float $cachedTotalRevenue = null;

    protected ?float $cachedNetProfit = null;

    protected ?float $cachedProfitMargin = null;

    public function getTotalPaidAttribute(): float
    {
        if (array_key_exists('total_paid', $this->attributes)) {
            return (float) $this->attributes['total_paid'];
        }

        return (float) $this->batchPayments()->sum('amount');
    }

    public function getBatchPaymentsCountAttribute(): int
    {
        if (array_key_exists('batch_payments_count', $this->attributes)) {
            return (int) $this->attributes['batch_payments_count'];
        }

        return (int) $this->batchPayments()->count();
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

    public function getTotalFeedConsumedAttribute(): float
    {
        if ($this->relationLoaded('dailyFeedIssues')) {
            return (float) $this->dailyFeedIssues->sum('quantity');
        }

        return (float) $this->dailyFeedIssues()->sum('quantity');
    }

    public function getTotalFeedCostAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_feed_cost'] !== null
        ) {
            return (float) $this->attributes['total_feed_cost'];
        }

        if ($this->cachedTotalFeedCost !== null) {
            return $this->cachedTotalFeedCost;
        }

        $issues = $this->dailyFeedIssues()
            ->with(['feedItem'])
            ->get();

        if ($issues->isEmpty()) {
            $this->cachedTotalFeedCost = 0.0;

            return 0.0;
        }

        $feedStocks = FeedStock::query()
            ->whereIn('feed_item_id', $issues->pluck('feed_item_id')->filter()->unique())
            ->whereIn('feed_warehouse_id', $issues->pluck('feed_warehouse_id')->filter()->unique())
            ->get()
            ->keyBy(function ($stock) {
                return $stock->feed_item_id.'-'.$stock->feed_warehouse_id;
            });

        $totalCost = 0.0;

        foreach ($issues as $issue) {
            $key = ($issue->feed_item_id ?? '').'-'.($issue->feed_warehouse_id ?? '');
            $feedStock = $feedStocks->get($key);

            $costPerUnit = $feedStock?->average_cost ?? ($issue->feedItem?->standard_cost ?? 0);

            $totalCost += (float) $issue->quantity * (float) $costPerUnit;
        }

        $this->cachedTotalFeedCost = (float) $totalCost;

        return $this->cachedTotalFeedCost;
    }

    public function getAllocatedExpensesAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_operating_expenses'] !== null
        ) {
            return (float) $this->attributes['total_operating_expenses'];
        }

        if ($this->cachedAllocatedExpenses !== null) {
            return $this->cachedAllocatedExpenses;
        }

        $voucherTotal = (float) $this->vouchers()->sum('amount');

        $pettyCashTotal = (float) $this->pettyCashTransactions()
            ->where('type', 'expense')
            ->sum('amount');

        $this->cachedAllocatedExpenses = $voucherTotal + $pettyCashTotal;

        return $this->cachedAllocatedExpenses;
    }

    public function getTotalCycleExpensesAttribute(): float
    {
        if ($this->cachedTotalCycleExpenses !== null) {
            return $this->cachedTotalCycleExpenses;
        }

        $hatcheryCost = (float) ($this->total_cost ?? 0);
        $feedCost = $this->total_feed_cost;
        $operatingExpenses = $this->allocated_expenses;

        $this->cachedTotalCycleExpenses = $hatcheryCost + $feedCost + $operatingExpenses;

        return $this->cachedTotalCycleExpenses;
    }

    public function getTotalRevenueAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['total_revenue'] !== null
        ) {
            return (float) $this->attributes['total_revenue'];
        }

        if ($this->cachedTotalRevenue !== null) {
            return $this->cachedTotalRevenue;
        }

        $this->cachedTotalRevenue = (float) \App\Models\OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->has('salesOrders') // Must be sold
                    ->whereHas('harvestOperation', function ($hop) {
                        $hop->where('batch_id', $this->id);
                    });
            })
            ->sum('subtotal');

        return $this->cachedTotalRevenue;
    }

    public function getNetProfitAttribute(): float
    {
        // If cycle is closed, return saved value
        if (
            $this->is_cycle_closed &&
            $this->attributes['net_profit'] !== null
        ) {
            return (float) $this->attributes['net_profit'];
        }

        if ($this->cachedNetProfit !== null) {
            return $this->cachedNetProfit;
        }

        $this->cachedNetProfit = $this->total_revenue - $this->total_cycle_expenses;

        return $this->cachedNetProfit;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cachedProfitMargin !== null) {
            return $this->cachedProfitMargin;
        }

        if ($this->total_revenue <= 0) {
            $this->cachedProfitMargin = 0.0;

            return 0.0;
        }

        $this->cachedProfitMargin = ($this->net_profit / $this->total_revenue) * 100;

        return $this->cachedProfitMargin;
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
