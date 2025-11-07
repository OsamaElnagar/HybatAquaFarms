<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Filament\Resources\FeedMovements\FeedMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedMovements extends ListRecords
{
    protected static string $resource = FeedMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
