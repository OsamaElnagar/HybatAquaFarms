<?php

namespace App\Filament\Resources\FeedStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('المستودع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_in_stock')
                    ->label('الكمية في المخزون')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->sortable(),
                TextColumn::make('average_cost')
                    ->label('متوسط التكلفة')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('القيمة الإجمالية')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
