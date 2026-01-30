<?php

namespace App\Filament\Resources\PettyCashes\Pages;

use App\Filament\Resources\PettyCashes\Infolists\PettyCashInfolist;
use App\Filament\Resources\PettyCashes\PettyCashResource;
use App\Filament\Resources\PettyCashes\Widgets\PettyCashStatsWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPettyCash extends ViewRecord
{
    protected static string $resource = PettyCashResource::class;

    public function getTitle(): string
    {
        return 'عرض العهدة: '.$this->getRecord()->name;
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
        return PettyCashInfolist::configure($schema);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PettyCashStatsWidget::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
