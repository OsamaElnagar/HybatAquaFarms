<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Filament\Resources\EggCollections\EggCollectionResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EggCollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'eggCollections';

    protected static ?string $title = 'جمع البيض';

    protected static ?string $modelLabel = 'جمع البيض';

    protected static ?string $pluralModelLabel = 'جمع البيض';

    public function form(Schema $schema): Schema
    {
        return EggCollectionResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return EggCollectionResource::table($table)
            ->recordTitleAttribute('collection_number')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
