<?php

namespace App\Filament\Resources\Traders;

use App\Filament\Resources\Traders\Pages\CreateTrader;
use App\Filament\Resources\Traders\Pages\EditTrader;
use App\Filament\Resources\Traders\Pages\ListTraders;
use App\Filament\Resources\Traders\Schemas\TraderForm;
use App\Filament\Resources\Traders\Tables\TradersTable;
use App\Models\Trader;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TraderResource extends Resource
{
    protected static ?string $model = Trader::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'الشركاء';
    }

    public static function getNavigationLabel(): string
    {
        return 'التجار';
    }

    public static function getModelLabel(): string
    {
        return 'تاجر';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التجار';
    }

    public static function form(Schema $schema): Schema
    {
        return TraderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradersTable::configure($table);
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
            'index' => ListTraders::route('/'),
            'create' => CreateTrader::route('/create'),
            'edit' => EditTrader::route('/{record}/edit'),
        ];
    }
}
