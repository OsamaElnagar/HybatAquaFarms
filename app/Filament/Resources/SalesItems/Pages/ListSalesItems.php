<?php

namespace App\Filament\Resources\SalesItems\Pages;

use App\Filament\Resources\SalesItems\SalesItemResource;
use App\Filament\Resources\SalesItems\Widgets\SalesItemsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesItems extends ListRecords
{
    protected static string $resource = SalesItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesItemsStatsWidget::class,
        ];
    }
}
