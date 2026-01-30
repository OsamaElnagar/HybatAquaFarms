<?php

namespace App\Filament\Resources\Farms\Pages;

use App\Filament\Resources\Farms\FarmResource;
use App\Filament\Resources\Farms\Infolists\FarmInfolist;
use App\Filament\Resources\Farms\Widgets\FeedConsumptionWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFarm extends ViewRecord
{
    protected static string $resource = FarmResource::class;

    public function getTitle(): string
    {
        return 'عرض مزرعة: '.$this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            '#' => $this->getRecord()->name,
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
        return FarmInfolist::configure($schema);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeedConsumptionWidget::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
