<?php

namespace App\Filament\Resources\FarmUnits;

use App\Filament\Resources\FarmUnits\Pages\CreateFarmUnit;
use App\Filament\Resources\FarmUnits\Pages\EditFarmUnit;
use App\Filament\Resources\FarmUnits\Pages\ListFarmUnits;
use App\Filament\Resources\FarmUnits\Schemas\FarmUnitForm;
use App\Filament\Resources\FarmUnits\Tables\FarmUnitsTable;
use App\Models\FarmUnit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FarmUnitResource extends Resource
{
    protected static ?string $model = FarmUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Square2Stack;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'farm.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->farm->name.' - '.$record->code;
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
        return FarmUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FarmUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BatchesRelationManager::class,
            RelationManagers\DailyFeedIssuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFarmUnits::route('/'),
            'create' => CreateFarmUnit::route('/create'),
            'view' => Pages\ViewFarmUnit::route('/{record}'),
            'edit' => EditFarmUnit::route('/{record}/edit'),
        ];
    }
}
