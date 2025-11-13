<?php

namespace App\Filament\Resources\ClearingEntries;

use App\Filament\Resources\ClearingEntries\Pages\CreateClearingEntry;
use App\Filament\Resources\ClearingEntries\Pages\EditClearingEntry;
use App\Filament\Resources\ClearingEntries\Pages\ListClearingEntries;
use App\Filament\Resources\ClearingEntries\Schemas\ClearingEntryForm;
use App\Filament\Resources\ClearingEntries\Tables\ClearingEntriesTable;
use App\Models\ClearingEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClearingEntryResource extends Resource
{
    protected static ?string $model = ClearingEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    public static function getNavigationGroup(): ?string
    {
        return 'المالية';
    }

    public static function getNavigationLabel(): string
    {
        return 'التسويات';
    }

    public static function getModelLabel(): string
    {
        return 'تسوية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التسويات';
    }

    public static function form(Schema $schema): Schema
    {
        return ClearingEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClearingEntriesTable::configure($table);
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
            'index' => ListClearingEntries::route('/'),
            'create' => CreateClearingEntry::route('/create'),
            'edit' => EditClearingEntry::route('/{record}/edit'),
        ];
    }
}
