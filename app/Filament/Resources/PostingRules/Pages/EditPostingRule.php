<?php

namespace App\Filament\Resources\PostingRules\Pages;

use App\Filament\Resources\PostingRules\PostingRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostingRule extends EditRecord
{
    protected static string $resource = PostingRuleResource::class;

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
