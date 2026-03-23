<?php

namespace App\Filament\Resources\EggSales\Pages;

use App\Filament\Resources\EggSales\EggSaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEggSales extends ListRecords
{
    protected static string $resource = EggSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
