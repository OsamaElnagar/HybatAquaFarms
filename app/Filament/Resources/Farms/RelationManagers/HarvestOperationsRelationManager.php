<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\HarvestOperations\Schemas\HarvestOperationForm;
use App\Filament\Resources\HarvestOperations\Tables\HarvestOperationsTable;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HarvestOperationsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestOperations';

    protected static ?string $title = 'عمليات الحصاد';

    protected static ?string $modelLabel = 'عملية حصاد';

    protected static ?string $pluralModelLabel = 'عمليات حصاد';

    public function form(Schema $schema): Schema
    {
        return HarvestOperationForm::configure($schema);
    }

    public function table(Table $table): Table
    {

        return HarvestOperationsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
