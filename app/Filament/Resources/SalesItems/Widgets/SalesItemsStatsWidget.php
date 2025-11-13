<?php

namespace App\Filament\Resources\SalesItems\Widgets;

use App\Models\SalesItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesItemsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = SalesItem::count();
        $totalQty = (float) SalesItem::sum('quantity');
        $totalAmount = (float) SalesItem::sum('total_price');

        return [
            Stat::make('عناصر مبيعات', number_format($count))
                ->description('عدد الأسطر')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('إجمالي الكمية', number_format($totalQty, 2))
                ->description('كل العناصر')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),

            Stat::make('إجمالي المبلغ', number_format($totalAmount, 2).' ج.م')
                ->description('مجموع أسعار العناصر')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
