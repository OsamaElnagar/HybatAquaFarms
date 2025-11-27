<?php

namespace App\Filament\Resources\DailyFeedIssues\Widgets;

use App\Models\DailyFeedIssue;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DailyFeedIssuesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalIssues = DailyFeedIssue::count();
        $todayIssues = DailyFeedIssue::whereDate('date', Carbon::today())->count();
        $thisWeekIssues = DailyFeedIssue::whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $thisMonthIssues = DailyFeedIssue::whereMonth('date', Carbon::now()->month)->count();

        $todayQuantity = DailyFeedIssue::whereDate('date', Carbon::today())->sum('quantity');
        $thisWeekQuantity = DailyFeedIssue::whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('quantity');
        $thisMonthQuantity = DailyFeedIssue::whereMonth('date', Carbon::now()->month)->sum('quantity');

        return [
            Stat::make('إجمالي السجلات', number_format($totalIssues))
                ->description('عدد سجلات الصرف')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('صرف اليوم', number_format($todayQuantity))
                ->description($todayIssues.' سجل')
                ->descriptionIcon('heroicon-o-sun')
                ->color('success'),

            Stat::make('صرف هذا الأسبوع', number_format($thisWeekQuantity))
                ->description($thisWeekIssues.' سجل')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('صرف هذا الشهر', number_format($thisMonthQuantity))
                ->description($thisMonthIssues.' سجل')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
