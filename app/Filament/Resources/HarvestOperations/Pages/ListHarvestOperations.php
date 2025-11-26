<?php

namespace App\Filament\Resources\HarvestOperations\Pages;

use App\Filament\Resources\HarvestOperations\HarvestOperationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHarvestOperations extends ListRecords
{
    protected static string $resource = HarvestOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
