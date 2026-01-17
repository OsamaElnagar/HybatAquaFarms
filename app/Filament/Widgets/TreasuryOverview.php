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
        $summary = $service->getMonthlySummary();
        $lastMonthSummary = $service->getMonthlySummary(null, now()->subMonth()->format('Y-m'));
        $daily = $service->getDailySummary();
        $totalBalance = $service->getTreasuryBalance();

        return [
            Stat::make('إجمالي رصيد الخزنة', number_format($totalBalance, 0).' EGP')
                ->description('مجموع أرصدة الصناديق والبنوك')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('وارد اليوم', number_format($daily['incoming'], 0).' EGP')
                ->description('إجمالي المقبوضات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('صادر اليوم', number_format($daily['outgoing'], 0).' EGP')
                ->description('إجمالي المدفوعات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('وارد هذا الشهر', number_format($summary['incoming'], 0).' EGP')
                ->description('إجمالي المقبوضات هذا الشهر')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('صادر هذا الشهر', number_format($summary['outgoing'], 0).' EGP')
                ->description('إجمالي المدفوعات هذا الشهر')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('وارد الشهر الماضي', number_format($lastMonthSummary['incoming'], 0).' EGP')
                ->description('إجمالي المقبوضات الشهر الماضي')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('صادر الشهر الماضي', number_format($lastMonthSummary['outgoing'], 0).' EGP')
                ->description('إجمالي المدفوعات الشهر الماضي')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
