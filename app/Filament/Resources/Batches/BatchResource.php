<?php

namespace App\Filament\Resources\Batches;

use App\Filament\Resources\Batches\Pages\CreateBatch;
use App\Filament\Resources\Batches\Pages\EditBatch;
use App\Filament\Resources\Batches\Pages\ListBatches;
use App\Filament\Resources\Batches\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Batches\Schemas\BatchForm;
use App\Filament\Resources\Batches\Tables\BatchesTable;
use App\Models\Batch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    // sort
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'الزريعة';
    }

    public static function getNavigationLabel(): string
    {
        return 'الزريعة';
    }

    public static function getModelLabel(): string
    {
        return 'دفعة زريعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'دفعات الزريعة';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['batch_code', 'farm.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return 'دورة زريعة -  '.$record->batch_code;
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
        return BatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchesTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['farm', 'units', 'species', 'factory'])
            ->withSum('batchPayments as total_paid', 'amount')
            ->withCount('batchPayments as batch_payments_count');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            MovementsRelationManager::class,
            RelationManagers\HarvestOperationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatches::route('/'),
            'create' => CreateBatch::route('/create'),
            'view' => Pages\ViewBatch::route('/{record}'),
            'edit' => EditBatch::route('/{record}/edit'),
        ];
    }
}
