<?php

namespace App\Filament\Resources\FeedWarehouses;

use App\Filament\Resources\FeedWarehouses\Pages\CreateFeedWarehouse;
use App\Filament\Resources\FeedWarehouses\Pages\EditFeedWarehouse;
use App\Filament\Resources\FeedWarehouses\Pages\ListFeedWarehouses;
use App\Filament\Resources\FeedWarehouses\Schemas\FeedWarehouseForm;
use App\Filament\Resources\FeedWarehouses\Tables\FeedWarehousesTable;
use App\Models\FeedWarehouse;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FeedWarehouseResource extends Resource
{
    protected static ?string $model = FeedWarehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'farm.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name.' - '.$record->code;
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
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
            RelationManagers\StocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedWarehouses::route('/'),
            'create' => CreateFeedWarehouse::route('/create'),
            'view' => Pages\ViewFeedWarehouse::route('/{record}'),
            'edit' => EditFeedWarehouse::route('/{record}/edit'),
        ];
    }
}
