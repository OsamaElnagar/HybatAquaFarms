<?php

namespace App\Filament\Resources\BatchMovements\Pages;

use App\Filament\Resources\BatchMovements\BatchMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBatchMovement extends EditRecord
{
    protected static string $resource = BatchMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
