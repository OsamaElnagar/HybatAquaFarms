<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HarvestAndSalesStats extends BaseWidget
{
    protected $listeners = ['updateCharts' => '$refresh'];

    protected function getStats(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? null;
        $endDate = $filters['date_end'] ?? null;
        $farmId = $filters['farm_id'] ?? null;
        $batchId = $filters['batch_id'] ?? null;

        // 1. Total Harvested Weight (From Order Items)
        $weightQuery = \App\Models\OrderItem::query();
        $weightQuery->whereHas('order', function ($q) use ($startDate, $endDate, $farmId, $batchId) {
            // Order has harvest_id and harvest_operation_id
            if ($startDate) {
                $q->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('date', '<=', $endDate);
            }

            if ($farmId) {
                $q->whereHas('harvestOperation', function ($hop) use ($farmId) {
                    $hop->where('farm_id', $farmId);
                });
            }

            if ($batchId) {
                $q->whereHas('harvestOperation', function ($hop) use ($batchId) {
                    $hop->where('batch_id', $batchId);
                });
            }
        });

        $totalHarvestWeight = $weightQuery->sum('total_weight'); // In Kg

        // 2. Total Sales Revenue
        $salesQuery = SalesOrder::query();

        if ($startDate) {
            $salesQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('date', '<=', $endDate);
        }
        if ($farmId) {
            $salesQuery->where('farm_id', $farmId);
        }
        if ($batchId) {
            // SalesOrders don't directly have batch_id, we infer from linked orders -> harvestOperation
            $salesQuery->whereHas('orders.harvestOperation', function ($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }

        $totalRevenue = $salesQuery->sum('net_amount');

        // 3. Average Selling Price (Total Revenue / Total Sold Weight)
        // We assume all "SalesOrders" encompass "Sold" items.
        // We need weight of items IN those sales orders to account for partial sales logic if it existed (but here SalesOrder = Sold).

        // Re-use sales query filters to find sold weight
        $soldWeightQuery = \App\Models\OrderItem::query()
            ->whereHas('order.salesOrders', function ($q) use ($startDate, $endDate, $farmId) {
                // Same filters as sales query
                if ($startDate) {
                    $q->whereDate('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('date', '<=', $endDate);
                }
                if ($farmId) {
                    $q->where('farm_id', $farmId);
                }
                // Batch filter already handled by joining through order->harvestOperation in strict sense,
                // but here we filter the SalesOrder itself.
            });

        if ($batchId) {
            $soldWeightQuery->whereHas('order.harvestOperation', function ($hop) use ($batchId) {
                $hop->where('batch_id', $batchId);
            });
        }

        $soldWeight = $soldWeightQuery->sum('total_weight');
        $avgPrice = $soldWeight > 0 ? ($totalRevenue / $soldWeight) : 0;

        return [
            Stat::make('إجمالي الحصاد', number_format($totalHarvestWeight / 1000, 2).' طن')
                ->description('الوزن الصافي للحصاد')
                ->descriptionIcon('heroicon-m-scale')
                ->color('success'),

            Stat::make('إجمالي المبيعات', number_format($totalRevenue, 0).' جنيه')
                ->description('صافي المبيعات بعد العمولات')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('متوسط سعر البيع', number_format($avgPrice, 2).' جنيه/كجم')
                ->description('بناءً على الكميات المباعة')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
