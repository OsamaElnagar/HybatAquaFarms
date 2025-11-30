<?php

namespace App\Filament\Resources\EmployeeAdvances\Widgets;

use App\Enums\AdvanceStatus;
use App\Models\EmployeeAdvance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EmployeeAdvancesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalAmount = EmployeeAdvance::sum('amount');
        $outstandingBalance = EmployeeAdvance::sum('balance_remaining');
        $activeAdvances = EmployeeAdvance::where('status', AdvanceStatus::Active)->count();
        $completedAdvances = EmployeeAdvance::where('status', AdvanceStatus::Completed)->count();
        $thisMonthAmount = EmployeeAdvance::whereMonth('disbursement_date', Carbon::now()->month)
            ->whereYear('disbursement_date', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('إجمالي السلف', number_format($totalAmount).' EGP ')
                ->description('إجمالي مبالغ السلف المسجلة')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('الرصيد المتبقي', number_format($outstandingBalance).' EGP ')
                ->description('المبلغ المتبقي على الموظفين')
                ->descriptionIcon('heroicon-o-wallet')
                ->color($outstandingBalance > 0 ? 'warning' : 'success'),

            Stat::make('سلف نشطة', number_format($activeAdvances))
                ->description($completedAdvances.' مكتملة')
                ->descriptionIcon('heroicon-o-queue-list')
                ->color('info'),

            Stat::make('صرف هذا الشهر', number_format($thisMonthAmount).' EGP ')
                ->description('السلف المصروفة خلال الشهر الحالي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
