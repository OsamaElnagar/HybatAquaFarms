<?php

namespace App\Filament\Resources\Farms\Widgets;

use App\Enums\FarmStatus;
use App\Models\Farm;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FarmsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalFarms = Farm::count();
        $activeFarms = Farm::where('status', FarmStatus::Active)->count();
        $totalUnits = Farm::withCount('units')->get()->sum('units_count');
        $totalBatches = Farm::withCount('batches')->get()->sum('batches_count');

        // Feed consumption stats
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthFeedConsumed = Farm::query()->get()->sum(function ($farm) use ($thisMonthStart) {
            return $farm->getTotalFeedConsumed($thisMonthStart->format('Y-m-d'), now()->format('Y-m-d'));
        });

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $lastMonthFeedConsumed = Farm::query()->get()->sum(function ($farm) use ($lastMonthStart, $lastMonthEnd) {
            return $farm->getTotalFeedConsumed($lastMonthStart->format('Y-m-d'), $lastMonthEnd->format('Y-m-d'));
        });

        $activeBatchesCount = Farm::query()->get()->sum(function ($farm) {
            return $farm->active_batches_count;
        });

        $totalCurrentStock = Farm::query()->get()->sum(function ($farm) {
            return $farm->total_current_stock;
        });

        return [
            Stat::make('إجمالي المزارع', number_format($totalFarms))
                ->description($activeFarms.' مزرعة نشطة، '.$totalUnits.' وحدة')
                ->descriptionIcon('heroicon-o-home-modern')
                ->color('primary'),

            Stat::make('دفعات الزريعة النشطة', number_format($activeBatchesCount))
                ->description('إجمالي الكمية الحالية: '.number_format($totalCurrentStock))
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Stat::make('استهلاك العلف هذا الشهر', number_format($thisMonthFeedConsumed, 2).' كجم')
                ->description($this->getFeedConsumptionComparison($thisMonthFeedConsumed, $lastMonthFeedConsumed))
                ->descriptionIcon($thisMonthFeedConsumed > $lastMonthFeedConsumed ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($this->getFeedConsumptionColor($thisMonthFeedConsumed, $lastMonthFeedConsumed)),

            Stat::make('إجمالي الدفعات', number_format($totalBatches))
                ->description('عبر جميع المزارع والوحدات')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('info'),
        ];
    }

    protected function getFeedConsumptionComparison(float $current, float $previous): string
    {
        if ($previous == 0) {
            return 'لا توجد بيانات للشهر السابق';
        }

        $change = $current - $previous;
        $percentage = abs(($change / $previous) * 100);

        if ($change > 0) {
            return 'زيادة '.number_format($percentage, 1).'% عن الشهر السابق';
        } elseif ($change < 0) {
            return 'انخفاض '.number_format($percentage, 1).'% عن الشهر السابق';
        }

        return 'نفس استهلاك الشهر السابق';
    }

    protected function getFeedConsumptionColor(float $current, float $previous): string
    {
        if ($previous == 0) {
            return 'gray';
        }

        $change = (($current - $previous) / $previous) * 100;

        if ($change > 20) {
            return 'danger'; // Significant increase
        } elseif ($change > 10) {
            return 'warning'; // Moderate increase
        }

        return 'success'; // Normal or decreased
    }
}
