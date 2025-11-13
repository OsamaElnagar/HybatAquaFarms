<?php

namespace App\Filament\Resources\FarmUnits\Pages;

use App\Filament\Resources\FarmUnits\FarmUnitResource;
use App\Filament\Resources\FarmUnits\Infolists\FarmUnitInfolist;
use App\Filament\Resources\FarmUnits\Widgets\FeedConsumptionWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFarmUnit extends ViewRecord
{
    protected static string $resource = FarmUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return FarmUnitInfolist::configure($schema);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeedConsumptionWidget::class,
        ];
    }
}
