<?php

namespace App\Filament\Resources\DailyFeedIssues\Pages;

use App\Filament\Resources\DailyFeedIssues\DailyFeedIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyFeedIssue extends EditRecord
{
    protected static string $resource = DailyFeedIssueResource::class;

    public function getTitle(): string
    {
        return 'تعديل: '.$this->getRecord()->date->format('Y-m-d').' - '.($this->getRecord()->unit?->code ?? 'بدون وحدة');
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        $label = $record->date->format('Y-m-d').' - '.($record->unit?->code ?? 'بدون وحدة');

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $record]) => $label,
            '#' => 'تعديل',
        ];
    }

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
