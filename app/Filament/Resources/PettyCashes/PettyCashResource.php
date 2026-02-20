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
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class PettyCashResource extends Resource
{
    protected static ?string $model = PettyCash::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    public static function getNavigationGroup(): ?string
    {
        return 'العُهد والمصروفات';
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

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'farms.name', 'custodian.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
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
        return PettyCashForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PettyCashesTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['custodian'])
            ->withSum(['transactions as total_in' => fn($query) => $query->where('direction', 'in')], 'amount')
            ->withSum(['transactions as total_out' => fn($query) => $query->where('direction', 'out')], 'amount');
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
            'view' => Pages\ViewPettyCash::route('/{record}'),
            'edit' => EditPettyCash::route('/{record}/edit'),
        ];
    }
}
