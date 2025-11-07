<?php

namespace App\Filament\Resources\BatchMovements\Pages;

use App\Filament\Resources\BatchMovements\BatchMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatchMovements extends ListRecords
{
    protected static string $resource = BatchMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
