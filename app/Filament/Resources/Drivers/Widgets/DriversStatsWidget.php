<?php

namespace App\Filament\Resources\Drivers\Widgets;

use App\Models\Driver;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DriversStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Driver::count();
        $active = Driver::where('is_active', true)->count();

        return [
            Stat::make('السائقون', number_format($total))
                ->description($active.' نشط')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('غير نشط', number_format($total - $active))
                ->description('بحاجة للمراجعة')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($total - $active > 0 ? 'warning' : 'success'),
        ];
    }
}
