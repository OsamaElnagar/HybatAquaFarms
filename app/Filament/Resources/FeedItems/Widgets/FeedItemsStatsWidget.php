<?php

namespace App\Filament\Resources\FeedItems\Widgets;

use App\Models\FeedItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedItemsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = FeedItem::count();
        $active = FeedItem::where('is_active', true)->count();
        $avgCost = (float) FeedItem::avg('standard_cost');

        return [
            Stat::make('أنواع الأعلاف', number_format($total))
                ->description($active.' نشط')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary'),

            Stat::make('متوسط التكلفة', number_format($avgCost).' EGP ')
                ->description('التكلفة القياسية')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info'),
        ];
    }
}
