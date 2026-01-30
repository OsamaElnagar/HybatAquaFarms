<?php

namespace App\Filament\Resources\DailyFeedIssues\Pages;

use App\Filament\Resources\DailyFeedIssues\DailyFeedIssueResource;
use App\Filament\Resources\DailyFeedIssues\Infolists\DailyFeedIssueInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewDailyFeedIssue extends ViewRecord
{
    protected static string $resource = DailyFeedIssueResource::class;

    public function getTitle(): string
    {
        return 'عرض: '.$this->getRecord()->date->format('Y-m-d').' - '.($this->getRecord()->unit?->code ?? 'بدون وحدة');
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        $label = $record->date->format('Y-m-d').' - '.($record->unit?->code ?? 'بدون وحدة');

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            '#' => $label,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return DailyFeedIssueInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
