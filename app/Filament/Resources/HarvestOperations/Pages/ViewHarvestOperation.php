<?php

namespace App\Filament\Resources\HarvestOperations\Pages;

use App\Filament\Resources\HarvestOperations\HarvestOperationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHarvestOperation extends ViewRecord
{
    protected static string $resource = HarvestOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
