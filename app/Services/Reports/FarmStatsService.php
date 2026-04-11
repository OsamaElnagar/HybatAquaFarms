<?php

namespace App\Services\Reports;

use App\Enums\FarmExpenseType;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FarmStatsService
{
    /**
     * Fetch batches based on parameters.
     */
    public function getBatches(Farm $farm, array $filters): Collection
    {
        $query = Batch::where('farm_id', $farm->id);

        if (! empty($filters['batch_ids'])) {
            $query->whereIn('id', $filters['batch_ids']);
        } else {
            if ($filters['annual_basis'] ?? false) {
                $year = $filters['year'] ?? now()->year;
                $query->where(function ($q) use ($year) {
                    $q->whereYear('entry_date', $year)
                        ->orWhereYear('closure_date', $year);
                });
            }

            if (! empty($filters['start_date'])) {
                $query->where('entry_date', '>=', Carbon::parse($filters['start_date']));
            }

            if (! empty($filters['end_date'])) {
                $query->where(function ($q) use ($filters) {
                    $q->whereNull('closure_date')
                        ->orWhere('closure_date', '<=', Carbon::parse($filters['end_date']));
                });
            }
        }

        return $query->get();
    }

    /**
     * Fetch farm-level expenses and revenues based on parameters.
     */
    public function getFarmExpenses(Farm $farm, array $filters): Collection
    {
        $query = FarmExpense::where('farm_id', $farm->id);

        $startDate = null;
        $endDate = null;

        if ($filters['annual_basis'] ?? false) {
            $year = $filters['year'] ?? now()->year;
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            if (! empty($filters['start_date'])) {
                $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            }
            if (! empty($filters['end_date'])) {
                $endDate = Carbon::parse($filters['end_date'])->endOfDay();
            }
        }

        // Apply date filters if they exist
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Calculate aggregated statistics for a collection of batches and farm expenses.
     */
    public function calculateStats(Collection $batches, Collection $farmExpenses): array
    {
        $batchExpenses = 0.0;
        $batchRevenue = 0.0;

        foreach ($batches as $batch) {
            $batchExpenses += (float) $batch->total_cycle_expenses;
            $batchRevenue += (float) $batch->total_revenue;
        }

        $extraExpenses = 0.0;
        $extraRevenue = 0.0;

        foreach ($farmExpenses as $expense) {
            if ($expense->type === FarmExpenseType::Expense) {
                $extraExpenses += (float) $expense->amount;
            } elseif ($expense->type === FarmExpenseType::Revenue) {
                $extraRevenue += (float) $expense->amount;
            }
        }

        $totalExpenses = $batchExpenses + $extraExpenses;
        $totalRevenue = $batchRevenue + $extraRevenue;

        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0.0;

        return [
            'total_expenses' => $totalExpenses,
            'total_revenue' => $totalRevenue,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'batch_count' => $batches->count(),
            'extra_expenses' => $extraExpenses,
            'extra_revenue' => $extraRevenue,
            'batches' => $batches->map(fn ($b) => [
                'id' => $b->id,
                'code' => $b->batch_code,
                'expenses' => (float) $b->total_cycle_expenses,
                'revenue' => (float) $b->total_revenue,
                'profit' => (float) $b->net_profit,
                'margin' => (float) $b->profit_margin,
                'status' => $b->status->getLabel(),
                'entry_date' => $b->entry_date?->format('Y-m-d'),
                'closure_date' => $b->closure_date?->format('Y-m-d'),
            ])->toArray(),
            'other_transactions' => $farmExpenses->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->date?->format('Y-m-d'),
                'type' => $e->type->value,
                'type_label' => $e->type->getLabel(),
                'amount' => (float) $e->amount,
                'category' => $e->expenseCategory?->name ?? 'N/A',
                'description' => $e->description,
                'batch_code' => $e->batch?->batch_code ?? 'فرعي/مزرعة',
            ])->toArray(),
        ];
    }
}
