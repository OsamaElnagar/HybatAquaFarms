<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\Infolists\SalesOrderInfolist;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /*
            // Disabled: We now force Cash Sales on creation.
            Action::make('register_payment')
                ->label('تسجيل دفعة')
                // ...
            */
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return SalesOrderInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
