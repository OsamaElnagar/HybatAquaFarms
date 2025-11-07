<?php

namespace App\Filament\Resources\SalesItems\Pages;

use App\Filament\Resources\SalesItems\SalesItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesItem extends EditRecord
{
    protected static string $resource = SalesItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
