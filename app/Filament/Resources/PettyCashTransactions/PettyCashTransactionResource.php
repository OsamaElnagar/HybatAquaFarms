<?php

namespace App\Filament\Resources\PettyCashTransactions;

use App\Filament\Resources\PettyCashTransactions\Pages\CreatePettyCashTransaction;
use App\Filament\Resources\PettyCashTransactions\Pages\EditPettyCashTransaction;
use App\Filament\Resources\PettyCashTransactions\Pages\ListPettyCashTransactions;
use App\Filament\Resources\PettyCashTransactions\Schemas\PettyCashTransactionForm;
use App\Filament\Resources\PettyCashTransactions\Tables\PettyCashTransactionsTable;
use App\Models\PettyCashTransaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class PettyCashTransactionResource extends Resource
{
    protected static ?string $model = PettyCashTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    public static function getNavigationGroup(): ?string
    {
        return 'العُهدات';
    }

    public static function getNavigationLabel(): string
    {
        return 'معاملات العهد';
    }

    public static function getModelLabel(): string
    {
        return 'معاملة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'معاملات العهد';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['date'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return 'منصرف عهدة - '.$record->date->format('Y-m-d').' - '.$record->pettyCash->name;
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
            // 'create' => CreatePettyCashTransaction::route('/create'),
            // 'edit' => EditPettyCashTransaction::route('/{record}/edit'),
        ];
    }
}
