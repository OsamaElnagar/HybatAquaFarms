<?php

namespace App\Filament\Resources\ExternalCalculations;

use App\Filament\Resources\ExternalCalculations\Pages\CreateExternalCalculation;
use App\Filament\Resources\ExternalCalculations\Pages\EditExternalCalculation;
use App\Filament\Resources\ExternalCalculations\Pages\ListExternalCalculations;
use App\Filament\Resources\ExternalCalculations\Schemas\ExternalCalculationForm;
use App\Filament\Resources\ExternalCalculations\Tables\ExternalCalculationsTable;
use App\Models\ExternalCalculation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ExternalCalculationResource extends Resource
{
    protected static ?string $model = ExternalCalculation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة و المالية';
    }

    public static function getNavigationLabel(): string
    {
        return 'حسابات خارجية';
    }

    public static function getModelLabel(): string
    {
        return 'حساب خارجي';
    }

    public static function getPluralModelLabel(): string
    {
        return 'حسابات خارجية';
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalCalculationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalCalculationsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference_number', 'description'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return (string) ($record->reference_number ?: ('EC-'.$record->id)).' - '.$record->date->format('y-m-d');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalCalculations::route('/'),
            'create' => CreateExternalCalculation::route('/create'),
            'edit' => EditExternalCalculation::route('/{record}/edit'),
            'view' => \App\Filament\Resources\ExternalCalculations\Pages\ViewExternalCalculation::route('/{record}'),
        ];
    }
}
