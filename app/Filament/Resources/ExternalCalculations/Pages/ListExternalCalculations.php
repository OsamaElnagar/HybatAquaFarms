<?php

namespace App\Filament\Resources\ExternalCalculations\Pages;

use App\Filament\Resources\ExternalCalculations\ExternalCalculationResource;
use App\Filament\Resources\ExternalCalculations\Widgets\ExternalCalculationsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalCalculations extends ListRecords
{
    protected static string $resource = ExternalCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExternalCalculationsStatsWidget::class,
        ];
    }
}
