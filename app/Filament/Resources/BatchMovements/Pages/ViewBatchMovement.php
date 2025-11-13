<?php

namespace App\Filament\Resources\BatchMovements\Pages;

use App\Filament\Resources\BatchMovements\BatchMovementResource;
use App\Filament\Resources\BatchMovements\Infolists\BatchMovementInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBatchMovement extends ViewRecord
{
    protected static string $resource = BatchMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return BatchMovementInfolist::configure($schema);
    }
}
