<?php

namespace App\Filament\Resources\ClearingEntries\Pages;

use App\Filament\Resources\ClearingEntries\ClearingEntryResource;
use App\Filament\Resources\ClearingEntries\Widgets\ClearingEntriesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClearingEntries extends ListRecords
{
    protected static string $resource = ClearingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClearingEntriesStatsWidget::class,
        ];
    }
}
