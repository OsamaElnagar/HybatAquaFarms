<?php

namespace App\Filament\Resources\FarmUnits\Pages;

use App\Filament\Resources\FarmUnits\FarmUnitResource;
use App\Filament\Resources\FarmUnits\Widgets\FarmUnitsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFarmUnits extends ListRecords
{
    protected static string $resource = FarmUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FarmUnitsStatsWidget::class,
        ];
    }
}
