<?php

namespace App\Filament\Resources\AdvanceRepayments\Widgets;

use App\Models\AdvanceRepayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AdvanceRepaymentsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalAmount = AdvanceRepayment::sum('amount_paid');
        $outstanding = AdvanceRepayment::sum('balance_remaining');
        $thisMonth = AdvanceRepayment::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount_paid');
        $count = AdvanceRepayment::count();

        return [
            Stat::make('إجمالي السداد', number_format($totalAmount).' EGP ')
                ->description('عدد الدفعات: '.number_format($count))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('الرصيد المتبقي', number_format($outstanding).' EGP ')
                ->description('بعد السداد')
                ->descriptionIcon('heroicon-o-wallet')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make('سداد هذا الشهر', number_format($thisMonth).' EGP ')
                ->description('حسب تاريخ السداد')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
