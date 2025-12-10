<?php

namespace App\Filament\Widgets;

use App\Models\HarvestBox;
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

        // 1. Total Harvested Weight
        $harvestQuery = HarvestBox::query();

        $harvestQuery->whereHas('harvest', function ($q) use ($startDate, $endDate, $farmId, $batchId) {
            if ($startDate) {
                $q->whereDate('harvest_date', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('harvest_date', '<=', $endDate);
            }
            if ($farmId) {
                $q->where('farm_id', $farmId);
            }
            if ($batchId) {
                $q->where('batch_id', $batchId);
            }
        });

        $totalHarvestWeight = $harvestQuery->sum('weight'); // In Kg

        // 2. Total Sales Revenue
        // If batch is selected, we MUST look at harvest boxes linked to that batch
        // If NO batch is selected, we can look at sales orders directly (faster) BUT
        // to stay consistent with filters, let's look at boxes if batch is present.

        if ($batchId) {
            // Precise revenue for this batch only
            $totalRevenue = HarvestBox::query()
                ->where('batch_id', $batchId)
                ->where('is_sold', true)
                ->sum('subtotal');
        } else {
            // General Sales Revenue based on Order Filters
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
            $totalRevenue = $salesQuery->sum('net_amount');
        }

        // 3. Average Selling Price (Revenue / Sold Weight)
        $soldBoxesQuery = HarvestBox::query()->whereNotNull('sales_order_id');

        if ($batchId) {
            $soldBoxesQuery->where('batch_id', $batchId);
        } else {
            // Use same harvest/sales order filters
            $soldBoxesQuery->whereHas('salesOrder', function ($q) use ($startDate, $endDate, $farmId) {
                if ($startDate) {
                    $q->whereDate('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('date', '<=', $endDate);
                }
                if ($farmId) {
                    $q->where('farm_id', $farmId);
                }
            });
        }

        $soldWeight = $soldBoxesQuery->sum('weight');
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
