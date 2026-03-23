<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Filament\Resources\EggSales\EggSaleResource;
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

class EggSalesRelationManager extends RelationManager
{
    protected static string $relationship = 'eggSales';

    protected static ?string $title = 'مبيعات البيض';

    protected static ?string $modelLabel = 'مبيعات البيض';

    protected static ?string $pluralModelLabel = 'مبيعات البيض';

    public function form(Schema $schema): Schema
    {
        return EggSaleResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return EggSaleResource::table($table)
            ->recordTitleAttribute('sale_number')
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
