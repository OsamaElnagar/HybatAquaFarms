<?php

namespace App\Filament\Resources\Factories;

use App\Filament\Resources\Factories\Pages\CreateFactory;
use App\Filament\Resources\Factories\Pages\EditFactory;
use App\Filament\Resources\Factories\Pages\ListFactories;
use App\Filament\Resources\Factories\Schemas\FactoryForm;
use App\Filament\Resources\Factories\Tables\FactoriesTable;
use App\Models\Factory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FactoryResource extends Resource
{
    protected static ?string $model = Factory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    public static function getNavigationGroup(): ?string
    {
        return 'الشركاء';
    }

    public static function getNavigationLabel(): string
    {
        return 'المصانع | المفرخات';
    }

    public static function getModelLabel(): string
    {
        return 'مصنع أعلاف أو مفرخ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مصانع و مفرخات';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'phone', 'email'];
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
        return FactoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FactoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BatchesRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFactories::route('/'),
            'create' => CreateFactory::route('/create'),
            'view' => Pages\ViewFactory::route('/{record}'),
            'edit' => EditFactory::route('/{record}/edit'),
        ];
    }
}
