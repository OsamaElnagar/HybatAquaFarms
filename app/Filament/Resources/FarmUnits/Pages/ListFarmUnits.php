<?php

namespace App\Filament\Resources\FarmUnits\Pages;

use App\Filament\Resources\FarmUnits\FarmUnitResource;
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
}
