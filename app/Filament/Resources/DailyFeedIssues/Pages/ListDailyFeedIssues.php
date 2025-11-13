<?php

namespace App\Filament\Resources\DailyFeedIssues\Pages;

use App\Filament\Resources\DailyFeedIssues\DailyFeedIssueResource;
use App\Filament\Resources\DailyFeedIssues\Widgets\DailyFeedIssuesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyFeedIssues extends ListRecords
{
    protected static string $resource = DailyFeedIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailyFeedIssuesStatsWidget::class,
        ];
    }
}
