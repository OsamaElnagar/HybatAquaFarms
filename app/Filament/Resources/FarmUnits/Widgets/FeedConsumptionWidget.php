<?php

namespace App\Filament\Resources\FarmUnits\Widgets;

use App\Models\FarmUnit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FeedConsumptionWidget extends StatsOverviewWidget
{
    public ?FarmUnit $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $today = Carbon::today();
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisMonthStart = Carbon::now()->startOfMonth();

        $todayConsumption = $this->record->getTotalFeedConsumed($today->format('Y-m-d'), $today->format('Y-m-d'));
        $weekConsumption = $this->record->getTotalFeedConsumed($thisWeekStart->format('Y-m-d'), $today->format('Y-m-d'));
        $monthConsumption = $this->record->getTotalFeedConsumed($thisMonthStart->format('Y-m-d'), $today->format('Y-m-d'));
        $totalConsumption = $this->record->getTotalFeedConsumed();

        return [
            Stat::make('استهلاك العلف اليوم', number_format($todayConsumption, 2).' كجم')
                ->description('الاستهلاك اليومي للأعلاف')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('استهلاك العلف هذا الأسبوع', number_format($weekConsumption, 2).' كجم')
                ->description('من '.Carbon::now()->startOfWeek()->format('Y-m-d'))
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('استهلاك العلف هذا الشهر', number_format($monthConsumption, 2).' كجم')
                ->description('من '.Carbon::now()->startOfMonth()->format('Y-m-d'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make('إجمالي الاستهلاك', number_format($totalConsumption, 2).' كجم')
                ->description('منذ بداية التسجيل')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('warning'),
        ];
    }
}
