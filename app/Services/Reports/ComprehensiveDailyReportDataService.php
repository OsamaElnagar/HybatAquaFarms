<?php

namespace App\Services\Reports;

use App\Enums\ExternalCalculationType;
use App\Enums\FarmExpenseType;
use App\Models\Batch;
use App\Models\DailyFeedIssue;
use App\Models\EmployeeAdvance;
use App\Models\ExternalCalculation;
use App\Models\ExternalCalculationEntry;
use App\Models\Farm;
use App\Models\FarmExpense;
use App\Models\FeedStock;
use App\Models\Harvest;
use App\Models\JournalEntry;
use App\Models\OrderItem;
use App\Models\PettyCashTransaction;
use App\Models\SalesOrder;
use App\Models\Voucher;
use Carbon\Carbon;

class ComprehensiveDailyReportDataService
{
    public function gatherData(): array
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Treasury
        $treasuryService = app(\App\Services\TreasuryService::class);
        $dailyTreasury = $treasuryService->getDailySummary();
        $totalBalance = $treasuryService->getTreasuryBalance();

        $treasuryData = [
            'balance' => $totalBalance,
            'incoming' => $dailyTreasury['incoming'] ?? 0,
            'outgoing' => $dailyTreasury['outgoing'] ?? 0,
        ];

        // 2. Sales
        $salesTodayRevenue = SalesOrder::whereDate('date', $today)->sum('net_amount');
        $salesWeekRevenue = SalesOrder::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('net_amount');
        $salesMonthRevenue = SalesOrder::whereMonth('date', $now->month)->sum('net_amount');

        $salesTodayCount = SalesOrder::whereDate('date', $today)->count();
        $salesMonthCount = SalesOrder::whereMonth('date', $now->month)->count();

        // Used by existing PDF layout partially
        $salesQuery = SalesOrder::query()->whereDate('order_date', $today);
        $salesRevenueLegacy = (clone $salesQuery)->sum('total_after_discount');
        $oldSalesOrdersCount = (clone $salesQuery)->count();
        $soldWeightLegacy = OrderItem::query()
            ->whereHas('order.salesOrders', function ($q) use ($today) {
                $q->whereDate('order_date', $today);
            })->sum('total_weight');

        $salesData = [
            'today_revenue' => $salesTodayRevenue,
            'week_revenue' => $salesWeekRevenue,
            'month_revenue' => $salesMonthRevenue,
            'today_count' => $salesTodayCount,
            'month_count' => $salesMonthCount,
            // Legacy mapping for old layout
            'revenue' => $salesRevenueLegacy,
            'orders_count' => $oldSalesOrdersCount,
            'weight' => $soldWeightLegacy,
        ];

        // 3. Harvest
        $harvestsMonth = Harvest::with([
            'harvestOperation.batch.farm',
            'orders.items',
            'orders.salesOrders',
        ])
            ->whereMonth('harvest_date', $now->month)
            ->whereYear('harvest_date', $now->year)
            ->latest('harvest_date')
            ->get();

        $harvestMonthCount = $harvestsMonth->count();
        $harvestMonthWeight = 0;
        $harvestMonthBoxes = 0;
        $allSalesOrders = collect();

        foreach ($harvestsMonth as $harvest) {
            foreach ($harvest->orders as $order) {
                $harvestMonthWeight += $order->items->sum('total_weight');
                $harvestMonthBoxes += $order->items->sum('quantity');
                $allSalesOrders = $allSalesOrders->merge($order->salesOrders);
            }
        }
        $harvestMonthSalesAmount = $allSalesOrders->unique('id')->sum('net_amount');

        // Legacy map
        $harvestWeightLegacy = OrderItem::query()
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('date', $today);
            })->sum('total_weight');
        $harvestCountLegacy = Harvest::query()->whereDate('harvest_date', $today)->count();

        $harvestData = [
            'month_count' => $harvestMonthCount,
            'month_weight' => $harvestMonthWeight,
            'month_boxes' => $harvestMonthBoxes,
            'month_sales' => $harvestMonthSalesAmount,
            'latest' => $harvestsMonth->take(5), // Take last 5 for PDF display
            // Legacy mapping
            'weight' => $harvestWeightLegacy,
            'operations_count' => $harvestCountLegacy,
        ];

        // 4. Feed Stock
        $allStocks = FeedStock::with(['feedItem', 'warehouse.farm'])->get();
        $feedStockTotalWeight = $allStocks->sum('quantity_in_stock');
        $feedStockTotalValue = $allStocks->sum('total_value');
        $feedStockItemsCount = $allStocks->unique('feed_item_id')->count();
        $lowStocks = $allStocks->where('quantity_in_stock', '<=', 500)->sortBy('quantity_in_stock')->take(5);

        $feedStockData = [
            'total_weight' => $feedStockTotalWeight,
            'total_value' => $feedStockTotalValue,
            'items_count' => $feedStockItemsCount,
            'low_stocks' => $lowStocks,
        ];

        // 5. Active Batches
        $activeBatches = Batch::with(['farm', 'species', 'dailyFeedIssues'])
            ->where('is_cycle_closed', false)
            ->get();

        $batchesDetails = collect();
        foreach ($activeBatches as $batch) {
            $mortality = $batch->initial_quantity - $batch->current_quantity;
            $mortalityRate = $batch->initial_quantity > 0 ? round(($mortality / $batch->initial_quantity) * 100) : 0;

            $batchesDetails->push([
                'farm_name' => $batch->farm->name ?? 'مزرعة غير معروفة',
                'batch_code' => $batch->batch_code,
                'species_name' => $batch->species->name ?? 'مختلط/غير معروف',
                'days_active' => $batch->days_since_entry,
                'current_qty' => $batch->current_quantity,
                'initial_qty' => $batch->initial_quantity,
                'mortality_rate' => $mortalityRate,
                'initial_weight_avg' => $batch->initial_weight_avg,
                'current_weight_avg' => $batch->current_weight_avg,
                'total_feed_consumed' => $batch->total_feed_consumed,
                'total_cycle_expenses' => $batch->total_cycle_expenses,
                'outstanding_balance' => $batch->outstanding_balance,
            ]);
        }

        $batchesData = [
            'count' => $activeBatches->count(),
            'total_living' => $activeBatches->sum('current_quantity'),
            'total_feed' => $activeBatches->sum('total_feed_consumed'),
            'total_expenses' => $activeBatches->sum('total_cycle_expenses'),
            'details' => $batchesDetails,
        ];

        // 6. Daily Feed Issues
        $feedQueryLegacy = DailyFeedIssue::query()->whereDate('date', $today);
        $feedStatsLegacy = (clone $feedQueryLegacy)
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('COUNT(DISTINCT batch_id) as active_batches')
            ->first();

        $feedCostLegacy = (clone $feedQueryLegacy)
            ->join('feed_items', 'daily_feed_issues.feed_item_id', '=', 'feed_items.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('daily_feed_issues.quantity * feed_items.standard_cost'));

        // Get the latest 2 dates with feed issues
        $latestDates = DailyFeedIssue::select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->limit(2)
            ->pluck('date');

        $latestFeedIssues = DailyFeedIssue::with(['farm', 'feedItem', 'batch'])
            ->whereIn('date', $latestDates)
            ->orderBy('date', 'desc')
            ->get();

        // Group by Date -> Farm Name -> Item Name -> Sum of Quantity
        $groupedFeedIssues = $latestFeedIssues->groupBy(function ($issue) {
            return Carbon::parse($issue->date)->format('Y-m-d');
        })->map(function ($dateIssues) {
            return collect($dateIssues)->groupBy(function ($issue) {
                return $issue->farm ? $issue->farm->name : 'أخرى';
            })->map(function ($farmIssues) {
                return collect($farmIssues)->groupBy(function ($issue) {
                    return $issue->feedItem ? $issue->feedItem->name : 'علف غير محدد';
                })->map(function ($itemIssues) {
                    return collect($itemIssues)->sum('quantity');
                });
            });
        });

        $feedData = [
            'latest_grouped' => $groupedFeedIssues,
            'latest_list' => $latestFeedIssues, // The raw list of all issues for the latest dates
            // Legacy mapping
            'consumption' => $feedStatsLegacy->total_quantity ?? 0,
            'cost' => $feedCostLegacy,
            'active_batches' => $feedStatsLegacy->active_batches ?? 0,
        ];

        // 7. Expenses
        $outVouchers = Voucher::where('voucher_type', \App\Enums\VoucherType::Payment)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $outPettyCash = PettyCashTransaction::with(['pettyCash.farms'])
            ->where('direction', \App\Enums\PettyTransacionType::OUT)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalVouchers = $outVouchers->sum('amount');
        $totalPettyCash = $outPettyCash->sum('amount');
        $expensesTotalCount = $outVouchers->count() + $outPettyCash->count();

        // Calculate latest 25 combined transactions
        $combinedTransactions = collect();

        foreach ($outVouchers as $v) {
            $combinedTransactions->push([
                'date' => Carbon::parse($v->date),
                'amount' => $v->amount,
                'desc' => $v->description,
                'type' => 'سند',
            ]);
        }

        foreach ($outPettyCash as $p) {
            $locationName = 'عهدة';
            if ($p->pettyCash) {
                $locationName .= ' - '.$p->pettyCash->name;
                if ($p->pettyCash->farms->isNotEmpty()) {
                    $locationName .= ' ('.$p->pettyCash->farms->first()->name.')';
                }
            }

            $combinedTransactions->push([
                'date' => Carbon::parse($p->date),
                'amount' => $p->amount,
                'desc' => $p->description,
                'type' => $locationName,
            ]);
        }

        $latestExpenses = $combinedTransactions->sortByDesc('date')->take(25);

        $expensesData = [
            'total_vouchers' => $totalVouchers,
            'total_petty_cash' => $totalPettyCash,
            'grand_total' => $totalVouchers + $totalPettyCash,
            'count' => $expensesTotalCount,
            'latest' => $latestExpenses,
        ];

        // 8. Cashflow (Journal Entries)
        $entries = JournalEntry::whereMonth('date', $now->month)->get();
        $entriesCount = $entries->count();
        $entriesVolume = $entries->sum(function ($entry) {
            return $entry->total_debit ?? 0;
        });

        $cashflowData = [
            'count' => $entriesCount,
            'volume' => $entriesVolume,
            'latest' => $entries->sortByDesc('date')->take(5),
        ];

        // 9. Advances
        $activeAdvances = EmployeeAdvance::with('employee.farm')
            ->where('status', \App\Enums\AdvanceStatus::Active)
            ->get();

        $advancesCount = $activeAdvances->count();
        $advancesOriginalAmount = $activeAdvances->sum('amount');
        $advancesRemaining = $activeAdvances->sum('balance_remaining');

        $advancesData = [
            'count' => $advancesCount,
            'original_amount' => $advancesOriginalAmount,
            'remaining' => $advancesRemaining,
        ];

        // 10. External Calculations
        $receipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)->sum('amount');
        $payments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)->sum('amount');
        $net = $receipts - $payments;

        $monthReceipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        $monthPayments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        $accounts = ExternalCalculation::withSum(['entries as receipts_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Receipt)], 'amount')
            ->withSum(['entries as payments_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Payment)], 'amount')
            ->get();

        $externalCalculationsData = [
            'total_receipts' => $receipts,
            'total_payments' => $payments,
            'net_balance' => $net,
            'month_receipts' => $monthReceipts,
            'month_payments' => $monthPayments,
            'accounts' => $accounts,
        ];

        // 11. Farm Expenses
        $farmMonthExpenses = FarmExpense::where('type', FarmExpenseType::Expense)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $farmMonthRevenues = FarmExpense::where('type', FarmExpenseType::Revenue)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $farmMonthCount = FarmExpense::whereBetween('date', [$startOfMonth, $endOfMonth])->count();

        $farmExpensesByFarm = Farm::withSum(['farmExpenses as month_expenses' => fn ($q) => $q->where('type', FarmExpenseType::Expense)->whereBetween('date', [$startOfMonth, $endOfMonth])], 'amount')
            ->withSum(['farmExpenses as month_revenues' => fn ($q) => $q->where('type', FarmExpenseType::Revenue)->whereBetween('date', [$startOfMonth, $endOfMonth])], 'amount')
            ->whereHas('farmExpenses', fn ($q) => $q->whereBetween('date', [$startOfMonth, $endOfMonth]))
            ->get();

        $latestFarmExpenses = FarmExpense::with(['farm', 'expenseCategory'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        $farmExpensesData = [
            'month_expenses' => $farmMonthExpenses,
            'month_revenues' => $farmMonthRevenues,
            'month_net' => $farmMonthRevenues - $farmMonthExpenses,
            'month_count' => $farmMonthCount,
            'by_farm' => $farmExpensesByFarm,
            'latest' => $latestFarmExpenses,
        ];

        return [
            'report_date' => $today,
            'treasury' => $treasuryData,
            'sales' => $salesData,
            'harvest' => $harvestData,
            'feed_stock' => $feedStockData,
            'batches' => $batchesData,
            'feed' => $feedData,
            'expenses' => $expensesData,
            'cashflow' => $cashflowData,
            'advances' => $advancesData,
            'external_calculations' => $externalCalculationsData,
            'farm_expenses' => $farmExpensesData,
        ];
    }
}
