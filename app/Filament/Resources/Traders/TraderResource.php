<?php

namespace App\Filament\Resources\Traders;

use App\Filament\Resources\Traders\Pages\CreateTrader;
use App\Filament\Resources\Traders\Pages\EditTrader;
use App\Filament\Resources\Traders\Pages\ListTraders;
use App\Filament\Resources\Traders\Schemas\TraderForm;
use App\Filament\Resources\Traders\Tables\TradersTable;
use App\Models\Trader;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class TraderResource extends Resource
{
    protected static ?string $model = Trader::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name', 'phone', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name.' - '.$record->code;
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
        return TraderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SalesOrdersRelationManager::class,
            RelationManagers\ClearingEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTraders::route('/'),
            'create' => CreateTrader::route('/create'),
            'view' => Pages\ViewTrader::route('/{record}'),
            'edit' => EditTrader::route('/{record}/edit'),
        ];
    }
}
