<?php

namespace App\Filament\Resources\EmployeeAdvances;

use App\Filament\Resources\EmployeeAdvances\Pages\CreateEmployeeAdvance;
use App\Filament\Resources\EmployeeAdvances\Pages\EditEmployeeAdvance;
use App\Filament\Resources\EmployeeAdvances\Pages\ListEmployeeAdvances;
use App\Filament\Resources\EmployeeAdvances\Schemas\EmployeeAdvanceForm;
use App\Filament\Resources\EmployeeAdvances\Tables\EmployeeAdvancesTable;
use App\Models\EmployeeAdvance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeAdvanceResource extends Resource
{
    protected static ?string $model = EmployeeAdvance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'الموارد البشرية';
    }

    public static function getNavigationLabel(): string
    {
        return 'سُلف الموظفين';
    }

    public static function getModelLabel(): string
    {
        return 'سلفة موظف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'سُلف الموظفين';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeAdvanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAdvancesTable::configure($table);
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
            'index' => ListEmployeeAdvances::route('/'),
            'create' => CreateEmployeeAdvance::route('/create'),
            'edit' => EditEmployeeAdvance::route('/{record}/edit'),
        ];
    }
}
