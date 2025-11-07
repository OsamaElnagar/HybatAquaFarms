<?php

namespace App\Filament\Resources\BatchMovements;

use App\Filament\Resources\BatchMovements\Pages\CreateBatchMovement;
use App\Filament\Resources\BatchMovements\Pages\EditBatchMovement;
use App\Filament\Resources\BatchMovements\Pages\ListBatchMovements;
use App\Filament\Resources\BatchMovements\Schemas\BatchMovementForm;
use App\Filament\Resources\BatchMovements\Tables\BatchMovementsTable;
use App\Models\BatchMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatchMovementResource extends Resource
{
    protected static ?string $model = BatchMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // sort
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'الزريعة';
    }

    public static function getNavigationLabel(): string
    {
        return 'حركات الزريعة';
    }

    public static function getModelLabel(): string
    {
        return 'حركة زريعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'حركات الزريعة';
    }

    public static function form(Schema $schema): Schema
    {
        return BatchMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchMovementsTable::configure($table);
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
            'index' => ListBatchMovements::route('/'),
            'create' => CreateBatchMovement::route('/create'),
            'edit' => EditBatchMovement::route('/{record}/edit'),
        ];
    }
}
