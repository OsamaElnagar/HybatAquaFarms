<?php

namespace App\Filament\Resources\BatchMovements\Widgets;

use App\Enums\MovementType;
use App\Models\BatchMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BatchMovementsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        $totalMovements = BatchMovement::count();
        $todayMovements = BatchMovement::whereDate('date', $today)->count();
        $thisMonthMovements = BatchMovement::where('date', '>=', $thisMonth)->count();

        $totalMortality = BatchMovement::where('movement_type', MovementType::Mortality)
            ->sum('quantity');
        $thisMonthMortality = BatchMovement::where('movement_type', MovementType::Mortality)
            ->where('date', '>=', $thisMonth)
            ->sum('quantity');

        $totalHarvest = BatchMovement::where('movement_type', MovementType::Harvest)
            ->sum('quantity');
        $thisMonthHarvest = BatchMovement::where('movement_type', MovementType::Harvest)
            ->where('date', '>=', $thisMonth)
            ->sum('quantity');

        $totalTransfers = BatchMovement::where('movement_type', MovementType::Transfer)
            ->count();
        $thisMonthTransfers = BatchMovement::where('movement_type', MovementType::Transfer)
            ->where('date', '>=', $thisMonth)
            ->count();

        return [
            Stat::make('إجمالي الحركات', number_format($totalMovements))
                ->description('اليوم: '.number_format($todayMovements).' | هذا الشهر: '.number_format($thisMonthMovements))
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'),

            Stat::make('إجمالي النفوق', number_format($totalMortality))
                ->description('هذا الشهر: '.number_format($thisMonthMortality))
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('إجمالي الحصاد', number_format($totalHarvest))
                ->description('هذا الشهر: '.number_format($thisMonthHarvest))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('عمليات النقل', number_format($totalTransfers))
                ->description('هذا الشهر: '.number_format($thisMonthTransfers))
                ->descriptionIcon('heroicon-o-arrows-right-left')
                ->color('info'),
        ];
    }
}
