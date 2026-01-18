<?php

namespace App\Filament\Resources\FeedWarehouses\Pages;

use App\Filament\Resources\FeedWarehouses\FeedWarehouseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedWarehouse extends EditRecord
{
    protected static string $resource = FeedWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
