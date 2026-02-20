<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\FeedWarehouses\Schemas\FeedWarehouseForm;
use App\Filament\Resources\FeedWarehouses\Tables\FeedWarehousesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeedWarehousesRelationManager extends RelationManager
{
    protected static string $relationship = 'feedWarehouses';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'مخزن الأعلاف';
    }

    public function form(Schema $schema): Schema
    {
        return FeedWarehouseForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return FeedWarehousesTable::configure($table)
            ->headerActions([
                // Limit creation from here if necessary, but consistent table is good
            ]);
    }
}
