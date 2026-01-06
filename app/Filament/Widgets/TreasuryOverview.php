<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TreasuryOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $service = app(\App\Services\TreasuryService::class);
        $summary = $service->getDailySummary();
        $totalBalance = $service->getTreasuryBalance();

        return [
            Stat::make('إجمالي رصيد الخزنة', number_format($totalBalance, 0).' EGP')
                ->description('مجموع أرصدة الصناديق والبنوك')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('وارد اليوم', number_format($summary['incoming'], 0).' EGP')
                ->description('إجمالي المقبوضات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('صادر اليوم', number_format($summary['outgoing'], 0).' EGP')
                ->description('إجمالي المدفوعات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
