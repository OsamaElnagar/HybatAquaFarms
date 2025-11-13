<?php

namespace App\Filament\Resources\FeedWarehouses\RelationManagers;

use App\Filament\Resources\FeedStocks\Schemas\FeedStockForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    protected static ?string $title = 'أرصدة الأعلاف';

    public function form(Schema $schema): Schema
    {
        return FeedStockForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feedItem.name')
            ->columns([
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_in_stock')
                    ->label('الكمية المتاحة')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->color(fn ($state) => $state < 100 ? 'danger' : ($state < 500 ? 'warning' : 'success'))
                    ->sortable(),
                TextColumn::make('minimum_level')
                    ->label('الحد الأدنى')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->toggleable(),
                TextColumn::make('maximum_level')
                    ->label('الحد الأقصى')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->toggleable(),
                TextColumn::make('stock_value')
                    ->label('القيمة')
                    ->state(fn ($record) => number_format($record->quantity_in_stock * ($record->feedItem->standard_cost ?? 0), 2))
                    ->prefix('ج.م ')
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['feed_warehouse_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
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
}
