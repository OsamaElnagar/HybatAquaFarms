<?php

namespace App\Filament\Resources\FeedStocks\Pages;

use App\Filament\Resources\FeedStocks\FeedStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedStock extends EditRecord
{
    protected static string $resource = FeedStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
