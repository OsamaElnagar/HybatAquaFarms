<?php

namespace App\Filament\Resources\Batches\Widgets;

use App\Enums\BatchStatus;
use App\Models\Batch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BatchesStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $stats = Cache::remember('batches_stats_active_total_cost', 600, function () {
            $totalCost = Batch::where('status', BatchStatus::Active)
                ->whereNotNull('total_cost')
                ->sum('total_cost');

            $thisMonthBatches = Batch::where('status', BatchStatus::Active)
                ->whereMonth('entry_date', Carbon::now()->month)
                ->whereYear('entry_date', Carbon::now()->year)
                ->count();

            $thisMonthCost = Batch::where('status', BatchStatus::Active)
                ->whereNotNull('total_cost')
                ->whereMonth('entry_date', Carbon::now()->month)
                ->whereYear('entry_date', Carbon::now()->year)
                ->sum('total_cost');

            return [
                'total_cost' => $totalCost,
                'this_month_batches' => $thisMonthBatches,
                'this_month_cost' => $thisMonthCost,
            ];
        });

        return [

            Stat::make('إجمالي تكلفة الدفعات النشطة', number_format($stats['total_cost']).' EGP ')
                ->description('هذا الشهر: '.number_format($stats['this_month_batches']).' دفعة نشطة - '.number_format($stats['this_month_cost']).' EGP ')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

        ];
    }
}
