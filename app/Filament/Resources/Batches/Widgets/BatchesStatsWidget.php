<?php

namespace App\Filament\Resources\Batches\Widgets;

use App\Enums\BatchStatus;
use App\Models\Batch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BatchesStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalBatches = Batch::count();
        $activeBatches = Batch::where('status', BatchStatus::Active)->count();
        $harvestedBatches = Batch::where('status', BatchStatus::Harvested)->count();

        $totalInitialQuantity = Batch::sum('initial_quantity');
        $totalCurrentQuantity = Batch::sum('current_quantity');
        $mortalityCount = $totalInitialQuantity - $totalCurrentQuantity;
        $mortalityRate = $totalInitialQuantity > 0
            ? round(($mortalityCount / $totalInitialQuantity) * 100)
            : 0;

        $totalCost = Batch::whereNotNull('total_cost')->sum('total_cost');
        $thisMonthBatches = Batch::whereMonth('entry_date', Carbon::now()->month)
            ->whereYear('entry_date', Carbon::now()->year)
            ->count();

        $thisMonthCost = Batch::whereNotNull('total_cost')
            ->whereMonth('entry_date', Carbon::now()->month)
            ->whereYear('entry_date', Carbon::now()->year)
            ->sum('total_cost');

        return [
            Stat::make('الكمية الإجمالية', number_format($totalCurrentQuantity))
                ->description('من أصل '.number_format($totalInitialQuantity).' (نفوق: '.number_format($mortalityCount).' - '.$mortalityRate.'%)')
                ->descriptionIcon('heroicon-o-cube')
                ->color($mortalityRate > 10 ? 'danger' : ($mortalityRate > 5 ? 'warning' : 'success')),

            Stat::make('إجمالي الدفعات', number_format($totalBatches))
                ->description($activeBatches.' نشطة، '.$harvestedBatches.' محصودة')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary'),

            Stat::make('إجمالي التكلفة', number_format($totalCost).' EGP ')
                ->description('هذا الشهر: '.number_format($thisMonthBatches).' دفعة - '.number_format($thisMonthCost).' EGP ')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('متوسط الوزن الحالي', $this->getAverageWeight())
                ->description('متوسط وزن الوحدة الواحدة في جميع الدفعات النشطة')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }

    protected function getAverageWeight(): string
    {
        $avgWeight = Batch::where('status', BatchStatus::Active)
            ->whereNotNull('current_weight_avg')
            ->avg('current_weight_avg');

        if (! $avgWeight) {
            return 'لا توجد بيانات';
        }

        return number_format((float) $avgWeight).' جم';
    }
}
