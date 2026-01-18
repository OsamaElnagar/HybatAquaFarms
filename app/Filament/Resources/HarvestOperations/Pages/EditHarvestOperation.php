<?php

namespace App\Filament\Resources\HarvestOperations\Pages;

use App\Filament\Resources\HarvestOperations\HarvestOperationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHarvestOperation extends EditRecord
{
    protected static string $resource = HarvestOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
