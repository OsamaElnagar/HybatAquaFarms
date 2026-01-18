<?php

namespace App\Filament\Resources\DailyFeedIssues\Pages;

use App\Filament\Resources\DailyFeedIssues\DailyFeedIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyFeedIssue extends EditRecord
{
    protected static string $resource = DailyFeedIssueResource::class;

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
