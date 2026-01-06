<?php

namespace App\Filament\Resources\FeedMovements\Widgets;

use App\Enums\FeedMovementType;
use App\Models\FeedMovement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FeedMovementsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalMovements = FeedMovement::count();
        $inMovements = FeedMovement::where('movement_type', FeedMovementType::In)->count();
        $outMovements = FeedMovement::where('movement_type', FeedMovementType::Out)->count();
        $transferMovements = FeedMovement::where('movement_type', FeedMovementType::Transfer)->count();

        $thisMonthIn = FeedMovement::where('movement_type', FeedMovementType::In)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('quantity');

        $thisMonthOut = FeedMovement::where('movement_type', FeedMovementType::Out)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('quantity');

        return [
            Stat::make('إجمالي الحركات', number_format($totalMovements))
                ->description($inMovements.' وارد، '.$outMovements.' صادر، '.$transferMovements.' نقل')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'),

            Stat::make('الوارد هذا الشهر', number_format($thisMonthIn, 3))
                ->description('إجمالي الكمية الواردة')
                ->descriptionIcon('heroicon-o-arrow-down')
                ->color('success'),

            Stat::make('الصادر هذا الشهر', number_format($thisMonthOut, 3))
                ->description('إجمالي الكمية الصادرة')
                ->descriptionIcon('heroicon-o-arrow-up')
                ->color('warning'),

            Stat::make('صافي الحركة', number_format($thisMonthIn - $thisMonthOut, 3))
                ->description('الفرق بين الوارد والصادر')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($thisMonthIn - $thisMonthOut >= 0 ? 'info' : 'danger'),
        ];
    }
}
