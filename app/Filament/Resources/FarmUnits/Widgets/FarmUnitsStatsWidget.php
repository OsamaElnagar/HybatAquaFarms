<?php

namespace App\Filament\Resources\FarmUnits\Widgets;

use App\Enums\FarmStatus;
use App\Models\FarmUnit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FarmUnitsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalUnits = FarmUnit::count();
        $activeUnits = FarmUnit::where('status', FarmStatus::Active)->count();
        $unitsWithBatches = FarmUnit::has('batches')->count();
        $totalCapacity = FarmUnit::whereNotNull('capacity')->sum('capacity');

        $inactiveUnits = FarmUnit::where('status', FarmStatus::Inactive)->count();
        $maintenanceUnits = FarmUnit::where('status', FarmStatus::Maintenance)->count();

        return [
            Stat::make('إجمالي الوحدات', number_format($totalUnits))
                ->description($activeUnits.' نشطة، '.$inactiveUnits.' غير نشطة، '.$maintenanceUnits.' تحت الصيانة')
                ->descriptionIcon('heroicon-o-square-2-stack')
                ->color('primary'),

            Stat::make('الوحدات النشطة', number_format($activeUnits))
                ->description(number_format($unitsWithBatches).' وحدة تحتوي على دفعات')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('إجمالي السعة', $totalCapacity > 0 ? number_format($totalCapacity) : 'غير محدد')
                ->description('السعة الإجمالية لجميع الوحدات')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),

            Stat::make('وحدات بدون دفعات', number_format($totalUnits - $unitsWithBatches))
                ->description('وحدات فارغة جاهزة للاستخدام')
                ->descriptionIcon('heroicon-o-inbox')
                ->color($totalUnits - $unitsWithBatches > 0 ? 'warning' : 'success'),
        ];
    }
}
