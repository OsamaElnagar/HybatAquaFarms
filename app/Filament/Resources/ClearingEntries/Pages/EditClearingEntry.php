<?php

namespace App\Filament\Resources\ClearingEntries\Pages;

use App\Filament\Resources\ClearingEntries\ClearingEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClearingEntry extends EditRecord
{
    protected static string $resource = ClearingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
