<?php

namespace App\Filament\Resources\FeedWarehouses\Pages;

use App\Filament\Resources\FeedWarehouses\FeedWarehouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedWarehouses extends ListRecords
{
    protected static string $resource = FeedWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
