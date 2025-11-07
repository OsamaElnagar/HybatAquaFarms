<?php

namespace App\Filament\Resources\PettyCashes;

use App\Filament\Resources\PettyCashes\Pages\CreatePettyCash;
use App\Filament\Resources\PettyCashes\Pages\EditPettyCash;
use App\Filament\Resources\PettyCashes\Pages\ListPettyCashes;
use App\Filament\Resources\PettyCashes\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\PettyCashes\Schemas\PettyCashForm;
use App\Filament\Resources\PettyCashes\Tables\PettyCashesTable;
use App\Models\PettyCash;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PettyCashResource extends Resource
{
    protected static ?string $model = PettyCash::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'المالية';
    }

    public static function getNavigationLabel(): string
    {
        return 'العُهد';
    }

    public static function getModelLabel(): string
    {
        return 'عهدة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'العُهد';
    }

    public static function form(Schema $schema): Schema
    {
        return PettyCashForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PettyCashesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPettyCashes::route('/'),
            'create' => CreatePettyCash::route('/create'),
            'edit' => EditPettyCash::route('/{record}/edit'),
        ];
    }
}
