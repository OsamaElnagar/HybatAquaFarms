<?php

namespace App\Filament\Resources\FarmUnits;

use App\Filament\Resources\FarmUnits\Pages\CreateFarmUnit;
use App\Filament\Resources\FarmUnits\Pages\EditFarmUnit;
use App\Filament\Resources\FarmUnits\Pages\ListFarmUnits;
use App\Filament\Resources\FarmUnits\Schemas\FarmUnitForm;
use App\Filament\Resources\FarmUnits\Tables\FarmUnitsTable;
use App\Models\FarmUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FarmUnitResource extends Resource
{
    protected static ?string $model = FarmUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المزارع';
    }

    public static function getNavigationLabel(): string
    {
        return 'الأحواض والوحدات';
    }

    public static function getModelLabel(): string
    {
        return 'وحدة مزرعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الأحواض/الوحدات';
    }

    public static function form(Schema $schema): Schema
    {
        return FarmUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FarmUnitsTable::configure($table);
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
            'index' => ListFarmUnits::route('/'),
            'create' => CreateFarmUnit::route('/create'),
            'edit' => EditFarmUnit::route('/{record}/edit'),
        ];
    }
}
