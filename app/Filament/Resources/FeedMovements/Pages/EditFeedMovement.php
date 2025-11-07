<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Filament\Resources\FeedMovements\FeedMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedMovement extends EditRecord
{
    protected static string $resource = FeedMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
