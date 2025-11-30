<?php

namespace App\Filament\Resources\FactoryPayments\Widgets;

use App\Models\FactoryPayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FactoryPaymentsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = FactoryPayment::sum('amount');
        $count = FactoryPayment::count();
        $thisMonth = FactoryPayment::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('إجمالي مدفوعات المصانع', number_format($total).' EGP ')
                ->description('عدد العمليات: '.number_format($count))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('هذا الشهر', number_format($thisMonth).' EGP ')
                ->description('حسب التاريخ')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
