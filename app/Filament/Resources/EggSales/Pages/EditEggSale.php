<?php

namespace App\Filament\Resources\EggSales\Pages;

use App\Filament\Resources\EggSales\EggSaleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEggSale extends EditRecord
{
    protected static string $resource = EggSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
