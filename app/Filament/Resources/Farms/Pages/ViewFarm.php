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
}
