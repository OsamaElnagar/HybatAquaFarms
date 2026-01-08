<?php

namespace App\Filament\Resources\Traders\Pages;

use App\Filament\Resources\Traders\TraderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTraders extends ListRecords
{
    protected static string $resource = TraderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
