<?php

namespace App\Filament\Resources\HarvestOperations;

use App\Filament\Resources\HarvestOperations\Pages\CreateHarvestOperation;
use App\Filament\Resources\HarvestOperations\Pages\EditHarvestOperation;
use App\Filament\Resources\HarvestOperations\Pages\ListHarvestOperations;
use App\Filament\Resources\HarvestOperations\Pages\ViewHarvestOperation;
use App\Filament\Resources\HarvestOperations\Schemas\HarvestOperationForm;
use App\Filament\Resources\HarvestOperations\Tables\HarvestOperationsTable;
use App\Models\HarvestOperation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HarvestOperationResource extends Resource
{
    protected static ?string $model = HarvestOperation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function getNavigationGroup(): ?string
    {
        return 'الحصاد والمبيعات';
    }

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'عملية حصاد';

    protected static ?string $pluralModelLabel = 'عمليات الحصاد';

    protected static ?string $recordTitleAttribute = 'operation_number';

    public static function form(Schema $schema): Schema
    {
        return HarvestOperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HarvestOperationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HarvestsRelationManager::class,
            RelationManagers\HarvestBoxesRelationManager::class,
            RelationManagers\SalesOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHarvestOperations::route('/'),
            'create' => CreateHarvestOperation::route('/create'),
            'view' => ViewHarvestOperation::route('/{record}'),
            'edit' => EditHarvestOperation::route('/{record}/edit'),
        ];
    }
}
