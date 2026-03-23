<?php

namespace App\Filament\Resources\EggCollections\Pages;

use App\Filament\Resources\EggCollections\EggCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEggCollection extends EditRecord
{
    protected static string $resource = EggCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
