<?php

namespace App\Filament\Resources\DailyFeedIssues\Widgets;

use App\Models\DailyFeedIssue;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DailyFeedIssuesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('daily_feed_issues_stats', 600, function () {
            $totalIssues = DailyFeedIssue::count();

            // Today's stats
            $today = Carbon::today();
            $todayStats = DailyFeedIssue::whereDate('date', $today)
                ->selectRaw('count(*) as count, sum(quantity) as quantity')
                ->first();

            // This week's stats
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $weekStats = DailyFeedIssue::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->selectRaw('count(*) as count, sum(quantity) as quantity')
                ->first();

            // This month's stats
            $startOfMonth = Carbon::now()->startOfMonth();
            $monthStats = DailyFeedIssue::where('date', '>=', $startOfMonth)
                ->selectRaw('count(*) as count, sum(quantity) as quantity')
                ->first();

            return [
                Stat::make('إجمالي السجلات', number_format($totalIssues))
                    ->description('عدد سجلات الصرف')
                    ->descriptionIcon('heroicon-o-calendar-days')
                    ->color('primary'),

                Stat::make('صرف اليوم', number_format($todayStats->quantity ?? 0))
                    ->description(($todayStats->count ?? 0).' سجل')
                    ->descriptionIcon('heroicon-o-sun')
                    ->color('success'),

                Stat::make('صرف هذا الأسبوع', number_format($weekStats->quantity ?? 0))
                    ->description(($weekStats->count ?? 0).' سجل')
                    ->descriptionIcon('heroicon-o-calendar')
                    ->color('info'),

                Stat::make('صرف هذا الشهر', number_format($monthStats->quantity ?? 0))
                    ->description(($monthStats->count ?? 0).' سجل')
                    ->descriptionIcon('heroicon-o-chart-bar')
                    ->color('warning'),
            ];
        });
    }
}
