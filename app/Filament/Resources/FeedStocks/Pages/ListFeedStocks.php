<?php

namespace App\Filament\Resources\FeedStocks\Pages;

use App\Filament\Resources\FeedStocks\FeedStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedStocks extends ListRecords
{
    protected static string $resource = FeedStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
