<?php

namespace App\Filament\Resources\Traders\Pages;

use App\Filament\Resources\Traders\Infolists\TraderInfolist;
use App\Filament\Resources\Traders\TraderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewTrader extends ViewRecord
{
    protected static string $resource = TraderResource::class;

    public function getTitle(): string
    {
        return 'عرض: '.$this->getRecord()->name;
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
        return TraderInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
