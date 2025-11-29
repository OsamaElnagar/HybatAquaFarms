<?php

namespace App\Filament\Resources\Treasuries\Pages;

use App\Filament\Resources\Treasuries\TreasuryResource;
use App\Models\Treasury;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget;

class ListTreasuries extends ListRecords
{
    protected static string $resource = TreasuryResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::make([
                StatsOverviewWidget\Stat::make('نقدية', fn () => Treasury::getCashBalance())
                    ->description('رصيد الحسابات النقدية')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' ج.م'),
                StatsOverviewWidget\Stat::make('عهدة', fn () => Treasury::getPettyBalance())
                    ->description('رصيد العهود')
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' ج.م'),
                StatsOverviewWidget\Stat::make('إجمالي الخزينة', fn () => Treasury::getTotal())
                    ->description('إجمالي نقد + عهدة')
                    ->color('primary')

                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' ج.م'),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
