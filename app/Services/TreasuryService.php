<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Farm;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TreasuryService
{
    /**
     * Get the current treasury balance for a specific farm
     * Calculates by summing all debits minus credits for treasury accounts
     */
    public function getTreasuryBalance(?Farm $farm = null): float
    {
        $query = JournalLine::query()
            ->whereHas('account', function ($q) {
                $q->where('is_treasury', true);
            });

        if ($farm) {
            $query->where('farm_id', $farm->id);
        }

        $debits = (float) $query->sum('debit');
        $credits = (float) $query->sum('credit');

        return $debits - $credits;
    }

    /**
     * Get treasury balance for a specific account
     */
    public function getAccountBalance(Account $account): float
    {
        $debits = (float) $account->journalLines()->sum('debit');
        $credits = (float) $account->journalLines()->sum('credit');

        return $debits - $credits;
    }

    /**
     * Record a cash receipt (money IN)
     */
    public function recordReceipt(
        Account $treasuryAccount,
        float $amount,
        string $description,
        Model $source,
        ?Account $revenueAccount = null,
        array $metadata = []
    ): JournalEntry {
        return DB::transaction(function () use (
            $treasuryAccount,
            $amount,
            $description,
            $source,
            $revenueAccount,
            $metadata
        ) {
            // If no revenue account specified, use a default "Sales Revenue" or "Other Income"
            if (! $revenueAccount) {
                $revenueAccount = Account::where('code', 'REVENUE')->first();
            }

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-TREAS-'.now()->format('YmdHis').'-'.rand(100, 999),
                'date' => $metadata['date'] ?? now(),
                'description' => $description,
                'source_type' => get_class($source),
                'source_id' => $source->id,
                'is_posted' => true,
                'posted_by' => auth('web')->id(),
                'posted_at' => now(),
            ]);

            // Debit treasury (increase cash)
            $journalEntry->lines()->create([
                'account_id' => $treasuryAccount->id,
                'farm_id' => $treasuryAccount->farm_id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ]);

            // Credit revenue (increase revenue)
            $journalEntry->lines()->create([
                'account_id' => $revenueAccount->id,
                'farm_id' => $revenueAccount->farm_id ?? $treasuryAccount->farm_id,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ]);

            return $journalEntry;
        });
    }

    /**
     * Record a cash payment (money OUT)
     */
    public function recordPayment(
        Account $treasuryAccount,
        float $amount,
        string $description,
        Model $source,
        ?Account $expenseAccount = null,
        array $metadata = []
    ): JournalEntry {
        return DB::transaction(function () use (
            $treasuryAccount,
            $amount,
            $description,
            $source,
            $expenseAccount,
            $metadata
        ) {
            // If no expense account specified, use a default "Other Expenses"
            if (! $expenseAccount) {
                $expenseAccount = Account::where('code', 'EXPENSE')->first();
            }

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-TREAS-'.now()->format('YmdHis').'-'.rand(100, 999),
                'date' => $metadata['date'] ?? now(),
                'description' => $description,
                'source_type' => get_class($source),
                'source_id' => $source->id,
                'is_posted' => true,
                'posted_by' => auth('web')->id(),
                'posted_at' => now(),
            ]);

            // Debit expense (increase expense)
            $journalEntry->lines()->create([
                'account_id' => $expenseAccount->id,
                'farm_id' => $expenseAccount->farm_id ?? $treasuryAccount->farm_id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ]);

            // Credit treasury (decrease cash)
            $journalEntry->lines()->create([
                'account_id' => $treasuryAccount->id,
                'farm_id' => $treasuryAccount->farm_id,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ]);

            return $journalEntry;
        });
    }

    /**
     * Get treasury transactions for a date range
     */
    public function getTransactions(
        ?Farm $farm = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $query = JournalLine::query()
            ->with(['account', 'journalEntry.source'])
            ->whereHas('account', function ($q) {
                $q->where('is_treasury', true);
            });

        if ($farm) {
            $query->where('farm_id', $farm->id);
        }

        if ($startDate) {
            $query->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('date', '>=', $startDate);
            });
        }

        if ($endDate) {
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('date', '<=', $endDate);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get daily treasury summary
     */
    public function getDailySummary(?Farm $farm = null, ?string $date = null): array
    {
        $date = $date ?? today()->toDateString();

        $query = JournalLine::query()
            ->whereHas('account', function ($q) {
                $q->where('is_treasury', true);
            })
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->whereDate('date', $date);
            });

        if ($farm) {
            $query->where('farm_id', $farm->id);
        }

        $totalIncoming = (float) $query->sum('debit');
        $totalOutgoing = (float) $query->sum('credit');

        return [
            'date' => $date,
            'incoming' => $totalIncoming,
            'outgoing' => $totalOutgoing,
            'net' => $totalIncoming - $totalOutgoing,
        ];
    }

    public function getMonthlySummary(?Farm $farm = null, ?string $month = null): array
    {
        $base = $month ? Carbon::parse($month) : now();
        $startDate = $base->copy()->startOfMonth()->toDateString();
        $endDate = $base->copy()->endOfMonth()->toDateString();

        $query = JournalLine::query()
            ->whereHas('account', function ($q) {
                $q->where('is_treasury', true);
            })
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
            });

        if ($farm) {
            $query->where('farm_id', $farm->id);
        }

        $totalIncoming = (float) $query->sum('debit');
        $totalOutgoing = (float) $query->sum('credit');

        return [
            'month' => $base->format('Y-m'),
            'incoming' => $totalIncoming,
            'outgoing' => $totalOutgoing,
            'net' => $totalIncoming - $totalOutgoing,
        ];
    }
}
