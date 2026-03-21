<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\SalesOrders\Tables\SalesOrdersTable;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SalesOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'salesOrders';

    protected static ?string $title = 'فواتير تجار - البيع';

    protected static ?string $modelLabel = 'فاتورة بيع';

    protected static ?string $pluralModelLabel = 'فواتير بيع';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return SalesOrdersTable::configure($table)
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }
}
