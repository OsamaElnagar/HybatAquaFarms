<?php

namespace App\Filament\Resources\SalaryRecords\Widgets;

use App\Models\SalaryRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalaryRecordsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalNet = SalaryRecord::sum('net_salary');
        $currentMonthNet = SalaryRecord::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('net_salary');
        $pendingCount = SalaryRecord::where('status', 'pending')->count();
        $paidCount = SalaryRecord::where('status', 'paid')->count();
        $averageNet = SalaryRecord::avg('net_salary') ?? 0;

        return [
            Stat::make('إجمالي صافي المرتبات', number_format($totalNet, 2).' ج.م')
                ->description('مجمل ما تم صرفه من مرتبات')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('صافي هذا الشهر', number_format($currentMonthNet, 2).' ج.م')
                ->description('صافي المرتبات المدفوعة خلال الشهر الحالي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('طلبات معلقة', number_format($pendingCount))
                ->description($paidCount.' مدفوعة')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('متوسط الصافي', number_format($averageNet, 2).' ج.م')
                ->description('متوسط صافي المرتب لكل سجل')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}

