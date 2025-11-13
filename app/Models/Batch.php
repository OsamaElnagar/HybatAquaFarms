<?php

namespace App\Models;

use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
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

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
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
}
