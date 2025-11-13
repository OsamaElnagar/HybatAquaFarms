<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

use App\Filament\Resources\SalesItems\Schemas\SalesItemForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'أصناف العملية';

    public function form(Schema $schema): Schema
    {
        return SalesItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('species.name')
                    ->label('النوع')
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع'),
                    ]),
                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->toggleable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 3)
                            ->suffix(' كجم'),
                    ]),
                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sales_order_id'] = $this->getOwnerRecord()->id;

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
