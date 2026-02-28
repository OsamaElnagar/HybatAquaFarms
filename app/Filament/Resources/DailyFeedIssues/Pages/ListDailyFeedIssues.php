<?php

namespace App\Filament\Resources\DailyFeedIssues\Pages;

use App\Filament\Exports\DailyFeedIssueExporter;
use App\Filament\Resources\DailyFeedIssues\DailyFeedIssueResource;
use App\Filament\Resources\DailyFeedIssues\Widgets\DailyFeedIssuesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyFeedIssues extends ListRecords
{
    protected static string $resource = DailyFeedIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->closeModalByClickingAway(false),

            ExportAction::make('export')
                ->exporter(DailyFeedIssueExporter::class),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailyFeedIssuesStatsWidget::class,
        ];
    }
}
