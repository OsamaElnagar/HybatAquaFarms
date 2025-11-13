<?php

namespace App\Filament\Resources\FeedStocks\Widgets;

use App\Models\FeedStock;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedStocksStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalStocks = FeedStock::count();
        $totalQuantity = FeedStock::sum('quantity_in_stock');
        $totalValue = FeedStock::sum('total_value');
        $averageValue = $totalStocks > 0 ? $totalValue / $totalStocks : 0;

        return [
            Stat::make('إجمالي الأرصدة', number_format($totalStocks))
                ->description('عدد أصناف الأعلاف في المخزون')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('primary'),

            Stat::make('إجمالي الكمية', number_format($totalQuantity, 3))
                ->description('الكمية الإجمالية في جميع المستودعات')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),

            Stat::make('القيمة الإجمالية', number_format($totalValue, 2).' ج.م')
                ->description('إجمالي قيمة المخزون')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('متوسط القيمة', number_format($averageValue, 2).' ج.م')
                ->description('متوسط قيمة كل رصيد')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('warning'),
        ];
    }
}
