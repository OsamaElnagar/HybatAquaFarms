<?php

namespace App\Filament\Resources\FeedStocks\Pages;

use App\Filament\Resources\FeedStocks\FeedStockResource;
use App\Filament\Resources\FeedStocks\Infolists\FeedStockInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFeedStock extends ViewRecord
{
    protected static string $resource = FeedStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return FeedStockInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
