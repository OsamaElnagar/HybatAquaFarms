<?php

namespace App\Filament\Resources\PostingRules\Pages;

use App\Filament\Resources\PostingRules\PostingRuleResource;
use App\Filament\Resources\PostingRules\Widgets\PostingRulesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostingRules extends ListRecords
{
    protected static string $resource = PostingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PostingRulesStatsWidget::class,
        ];
    }
}
