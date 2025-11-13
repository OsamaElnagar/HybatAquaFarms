<?php

namespace App\Filament\Resources\FeedItems\Pages;

use App\Filament\Resources\FeedItems\FeedItemResource;
use App\Filament\Resources\FeedItems\Widgets\FeedItemsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedItems extends ListRecords
{
    protected static string $resource = FeedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeedItemsStatsWidget::class,
        ];
    }
}
