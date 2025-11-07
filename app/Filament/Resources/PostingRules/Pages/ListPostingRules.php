<?php

namespace App\Filament\Resources\PostingRules\Pages;

use App\Filament\Resources\PostingRules\PostingRuleResource;
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
}
