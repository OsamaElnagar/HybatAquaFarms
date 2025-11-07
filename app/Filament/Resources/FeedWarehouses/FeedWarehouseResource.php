<?php

namespace App\Filament\Resources\FeedWarehouses;

use App\Filament\Resources\FeedWarehouses\Pages\CreateFeedWarehouse;
use App\Filament\Resources\FeedWarehouses\Pages\EditFeedWarehouse;
use App\Filament\Resources\FeedWarehouses\Pages\ListFeedWarehouses;
use App\Filament\Resources\FeedWarehouses\Schemas\FeedWarehouseForm;
use App\Filament\Resources\FeedWarehouses\Tables\FeedWarehousesTable;
use App\Models\FeedWarehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedWarehouseResource extends Resource
{
    protected static ?string $model = FeedWarehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'الأعلاف';
    }

    public static function getNavigationLabel(): string
    {
        return 'مخازن الأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'مخزن علف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مخازن الأعلاف';
    }

    public static function form(Schema $schema): Schema
    {
        return FeedWarehouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedWarehousesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedWarehouses::route('/'),
            'create' => CreateFeedWarehouse::route('/create'),
            'edit' => EditFeedWarehouse::route('/{record}/edit'),
        ];
    }
}
