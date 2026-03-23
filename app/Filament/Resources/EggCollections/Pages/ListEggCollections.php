<?php

namespace App\Filament\Resources\EggCollections\Pages;

use App\Filament\Resources\EggCollections\EggCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEggCollections extends ListRecords
{
    protected static string $resource = EggCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
