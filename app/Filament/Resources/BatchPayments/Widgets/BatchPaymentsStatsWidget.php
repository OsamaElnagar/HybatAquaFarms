<?php

namespace App\Filament\Resources\BatchPayments\Widgets;

use App\Models\BatchPayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BatchPaymentsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = BatchPayment::sum('amount');
        $count = BatchPayment::count();
        $thisMonth = BatchPayment::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $avg = $count > 0 ? ($total / $count) : 0;

        return [
            Stat::make('إجمالي المدفوعات', number_format($total).' EGP ')
                ->description('عدد العمليات: '.number_format($count))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('متوسط الدفع', number_format($avg).' EGP ')
                ->description('لكل عملية')
                ->descriptionIcon('heroicon-o-adjustments-vertical')
                ->color('info'),

            Stat::make('مدفوعات هذا الشهر', number_format($thisMonth).' EGP ')
                ->description('حسب التاريخ')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
