<?php

namespace App\Filament\Resources\FeedStocks;

use App\Filament\Resources\FeedStocks\Pages\CreateFeedStock;
use App\Filament\Resources\FeedStocks\Pages\EditFeedStock;
use App\Filament\Resources\FeedStocks\Pages\ListFeedStocks;
use App\Filament\Resources\FeedStocks\Schemas\FeedStockForm;
use App\Filament\Resources\FeedStocks\Tables\FeedStocksTable;
use App\Models\FeedStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedStockResource extends Resource
{
    protected static ?string $model = FeedStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'الأعلاف';
    }

    public static function getNavigationLabel(): string
    {
        return 'أرصدة الأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'رصيد علف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'أرصدة الأعلاف';
    }

    public static function form(Schema $schema): Schema
    {
        return FeedStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedStocksTable::configure($table);
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
            'index' => ListFeedStocks::route('/'),
            'create' => CreateFeedStock::route('/create'),
            'edit' => EditFeedStock::route('/{record}/edit'),
        ];
    }
}
