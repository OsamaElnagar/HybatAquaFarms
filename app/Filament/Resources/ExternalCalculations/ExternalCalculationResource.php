<?php

namespace App\Filament\Resources\ExternalCalculations;

use App\Filament\Resources\ExternalCalculations\Pages\CreateExternalCalculation;
use App\Filament\Resources\ExternalCalculations\Pages\EditExternalCalculation;
use App\Filament\Resources\ExternalCalculations\Pages\ListExternalCalculations;
use App\Filament\Resources\ExternalCalculations\Pages\ViewExternalCalculation;
use App\Filament\Resources\ExternalCalculations\RelationManagers\EntriesRelationManager;
use App\Models\ExternalCalculation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EntriesRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalCalculations::route('/'),
            'create' => CreateExternalCalculation::route('/create'),
            'edit' => EditExternalCalculation::route('/{record}/edit'),
            'view' => ViewExternalCalculation::route('/{record}'),
        ];
    }
}
