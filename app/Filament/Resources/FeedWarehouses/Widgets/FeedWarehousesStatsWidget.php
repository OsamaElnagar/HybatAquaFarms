<?php

namespace App\Filament\Resources\FeedWarehouses\Widgets;

use App\Models\FeedStock;
use App\Models\FeedWarehouse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class FeedWarehousesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('feed_warehouses_stats', 600, function () {
            $totalWarehouses = FeedWarehouse::count();
            $activeWarehouses = FeedWarehouse::where('is_active', true)->count();

            // Direct query on FeedStock instead of loading all warehouses and their relations
            $totalStockItems = FeedStock::count();
            $totalStockValue = (float) FeedStock::sum('total_value');

            return [
                Stat::make('إجمالي المخازن', number_format($totalWarehouses))
                    ->description($activeWarehouses.' نشط')
                    ->descriptionIcon('heroicon-o-building-storefront')
                    ->color('primary'),

                Stat::make('أصناف المخزون', number_format($totalStockItems))
                    ->description('عدد أصناف الأعلاف المخزنة')
                    ->descriptionIcon('heroicon-o-archive-box')
                    ->color('info'),

                Stat::make('قيمة المخزون', number_format($totalStockValue).' EGP ')
                    ->description('القيمة الإجمالية للأعلاف')
                    ->descriptionIcon('heroicon-o-currency-dollar')
                    ->color('success'),

                Stat::make('متوسط قيمة المخزن', $totalWarehouses > 0 ? number_format($totalStockValue / $totalWarehouses).' EGP ' : '0.00 EGP')
                    ->description('متوسط قيمة المخزون لكل مخزن')
                    ->descriptionIcon('heroicon-o-chart-bar')
                    ->color('warning'),
            ];
        });
    }
}
