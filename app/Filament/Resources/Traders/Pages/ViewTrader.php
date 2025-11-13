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
}
