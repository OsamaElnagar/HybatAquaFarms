<?php

namespace App\Filament\Resources\BatchMovements\Widgets;

use App\Enums\MovementType;
use App\Models\BatchMovement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BatchMovementsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalMovements = BatchMovement::count();
        $entryMovements = BatchMovement::where('movement_type', MovementType::Entry)->count();
        $transferMovements = BatchMovement::where('movement_type', MovementType::Transfer)->count();
        $harvestMovements = BatchMovement::where('movement_type', MovementType::Harvest)->count();
        $mortalityMovements = BatchMovement::where('movement_type', MovementType::Mortality)->count();

        $thisMonthMovements = BatchMovement::whereMonth('date', Carbon::now()->month)->count();
        $thisMonthQuantity = BatchMovement::whereMonth('date', Carbon::now()->month)->sum('quantity');

        return [
            Stat::make('إجمالي الحركات', number_format($totalMovements))
                ->description($entryMovements.' إدخال، '.$transferMovements.' نقل، '.$harvestMovements.' حصاد، '.$mortalityMovements.' نفوق')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'),

            Stat::make('حركات هذا الشهر', number_format($thisMonthMovements))
                ->description('عدد الحركات في الشهر الحالي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('الكمية هذا الشهر', number_format($thisMonthQuantity))
                ->description('إجمالي الكمية المتحركة')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Stat::make('متوسط الحركات/يوم', $thisMonthMovements > 0 ? number_format($thisMonthMovements / Carbon::now()->day, 1) : '0')
                ->description('متوسط عدد الحركات يومياً')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
