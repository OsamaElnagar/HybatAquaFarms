<?php

namespace App\Filament\Resources\FeedWarehouses\Widgets;

use App\Models\FeedWarehouse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedWarehousesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalWarehouses = FeedWarehouse::count();
        $activeWarehouses = FeedWarehouse::where('is_active', true)->count();

        $totalStockItems = FeedWarehouse::withCount('stocks')->get()->sum('stocks_count');
        $totalStockValue = FeedWarehouse::query()->get()->sum(function ($warehouse) {
            return $warehouse->stocks->sum(function ($stock) {
                return $stock->quantity_in_stock * ($stock->feedItem->standard_cost ?? 0);
            });
        });

        return [
            Stat::make('إجمالي المخازن', number_format($totalWarehouses))
                ->description($activeWarehouses.' نشط')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary'),

            Stat::make('أصناف المخزون', number_format($totalStockItems))
                ->description('عدد أصناف الأعلاف المخزنة')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('info'),

            Stat::make('قيمة المخزون', number_format($totalStockValue, 2).' ج.م')
                ->description('القيمة الإجمالية للأعلاف')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('متوسط قيمة المخزن', $totalWarehouses > 0 ? number_format($totalStockValue / $totalWarehouses, 2).' ج.م' : '0.00 ج.م')
                ->description('متوسط قيمة المخزون لكل مخزن')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
