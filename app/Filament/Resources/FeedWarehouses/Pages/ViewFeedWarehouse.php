<?php

namespace App\Filament\Resources\FeedWarehouses\Pages;

use App\Filament\Resources\FeedWarehouses\FeedWarehouseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedWarehouse extends ViewRecord
{
    protected static string $resource = FeedWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
