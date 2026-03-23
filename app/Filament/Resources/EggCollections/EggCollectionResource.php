<?php

namespace App\Filament\Resources\EggCollections;

use App\Filament\Resources\EggCollections\Pages\CreateEggCollection;
use App\Filament\Resources\EggCollections\Pages\EditEggCollection;
use App\Filament\Resources\EggCollections\Pages\ListEggCollections;
use App\Filament\Resources\EggCollections\Schemas\EggCollectionForm;
use App\Filament\Resources\EggCollections\Tables\EggCollectionsTable;
use App\Models\EggCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EggCollectionResource extends Resource
{
    protected static ?string $model = EggCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public static function getNavigationGroup(): ?string
    {
        return 'الدواجن';
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'collection_number';

    protected static ?string $modelLabel = 'تجميع بيض';

    protected static ?string $pluralModelLabel = 'تجميع البيض';

    public static function form(Schema $schema): Schema
    {
        return EggCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EggCollectionsTable::configure($table);
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
            'index' => ListEggCollections::route('/'),
            'create' => CreateEggCollection::route('/create'),
            'edit' => EditEggCollection::route('/{record}/edit'),
        ];
    }
}
