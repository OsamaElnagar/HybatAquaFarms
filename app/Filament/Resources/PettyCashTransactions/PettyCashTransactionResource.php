<?php

namespace App\Filament\Resources\PettyCashTransactions;

use App\Filament\Resources\PettyCashTransactions\Pages\CreatePettyCashTransaction;
use App\Filament\Resources\PettyCashTransactions\Pages\EditPettyCashTransaction;
use App\Filament\Resources\PettyCashTransactions\Pages\ListPettyCashTransactions;
use App\Filament\Resources\PettyCashTransactions\Schemas\PettyCashTransactionForm;
use App\Filament\Resources\PettyCashTransactions\Tables\PettyCashTransactionsTable;
use App\Models\PettyCashTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PettyCashTransactionResource extends Resource
{
    protected static ?string $model = PettyCashTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'المالية';
    }

    public static function getNavigationLabel(): string
    {
        return 'معاملات العهدة';
    }

    public static function getModelLabel(): string
    {
        return 'معاملة عهدة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'معاملات العهدة';
    }

    public static function form(Schema $schema): Schema
    {
        return PettyCashTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PettyCashTransactionsTable::configure($table);
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
            'index' => ListPettyCashTransactions::route('/'),
            'create' => CreatePettyCashTransaction::route('/create'),
            'edit' => EditPettyCashTransaction::route('/{record}/edit'),
        ];
    }
}
