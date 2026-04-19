<?php

namespace App\Filament\Resources\Drivers\RelationManagers;

use App\Filament\Resources\HarvestOperations\Tables\OrdersTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'أوردرات الأسماك المنقولة';

    public function table(Table $table): Table
    {
        return OrdersTable::configure($table)->header(null);
    }
}
