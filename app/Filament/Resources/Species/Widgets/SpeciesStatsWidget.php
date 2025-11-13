<?php

namespace App\Filament\Resources\Species\Widgets;

use App\Models\Species;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpeciesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Species::count();
        $active = Species::where('is_active', true)->count();

        return [
            Stat::make('الأنواع', number_format($total))
                ->description($active.' نشط')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('primary'),
        ];
    }
}
