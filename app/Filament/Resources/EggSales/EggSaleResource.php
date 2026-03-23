<?php

namespace App\Filament\Resources\EggSales;

use App\Filament\Resources\EggSales\Pages\CreateEggSale;
use App\Filament\Resources\EggSales\Pages\EditEggSale;
use App\Filament\Resources\EggSales\Pages\ListEggSales;
use App\Filament\Resources\EggSales\Schemas\EggSaleForm;
use App\Filament\Resources\EggSales\Tables\EggSalesTable;
use App\Models\EggSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EggSaleResource extends Resource
{
    protected static ?string $model = EggSale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'sale_number';

    protected static ?string $modelLabel = 'بيع بيض';

    protected static ?string $pluralModelLabel = 'بيع البيض';

    public static function getNavigationGroup(): ?string
    {
        return 'الدواجن';
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EggSaleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EggSalesTable::configure($table);
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
            'index' => ListEggSales::route('/'),
            'create' => CreateEggSale::route('/create'),
            'edit' => EditEggSale::route('/{record}/edit'),
        ];
    }
}
