<?php

namespace App\Filament\Resources\Species\Pages;

use App\Filament\Resources\Species\SpeciesResource;
use App\Filament\Resources\Species\Widgets\SpeciesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecies extends ListRecords
{
    protected static string $resource = SpeciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // SpeciesStatsWidget::class,
        ];
    }
}
