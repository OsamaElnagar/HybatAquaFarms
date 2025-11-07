<?php

namespace App\Filament\Resources\FarmUnits\Pages;

use App\Filament\Resources\FarmUnits\FarmUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFarmUnit extends EditRecord
{
    protected static string $resource = FarmUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
