<?php

namespace App\Filament\Resources\HarvestOperations;

use App\Filament\Resources\HarvestOperations\Pages\CreateHarvestOperation;
use App\Filament\Resources\HarvestOperations\Pages\EditHarvestOperation;
use App\Filament\Resources\HarvestOperations\Pages\ListHarvestOperations;
use App\Filament\Resources\HarvestOperations\Pages\ViewHarvestOperation;
use App\Filament\Resources\HarvestOperations\RelationManagers\HarvestsRelationManager;
use App\Filament\Resources\HarvestOperations\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\HarvestOperations\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\HarvestOperations\Schemas\HarvestOperationForm;
use App\Filament\Resources\HarvestOperations\Tables\HarvestOperationsTable;
use App\Models\HarvestOperation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'operation_number',
            'start_date',
            'farm.name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return 'صيادة - '.$record->farm->name.' - '.$record->operation_number;
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
        return HarvestOperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HarvestOperationsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['batch.species', 'farm']);
    }

    public static function getRelations(): array
    {
        return [
            HarvestsRelationManager::class,
            OrdersRelationManager::class,
            OrderItemsRelationManager::class,
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
