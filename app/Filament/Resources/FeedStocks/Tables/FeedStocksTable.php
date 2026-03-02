<?php

namespace App\Filament\Resources\FeedStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
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
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->suffix(fn($record) => ' ' . ($record->feedItem?->unit_of_measure ?? ''))
                    ->color(fn($state): string => $state > 0 ? 'success' : 'danger')
                    ->badge()
                    ->sortable()
                    ->summarize(Sum::make()->numeric(decimalPlaces: 0, locale: 'en')),

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
                \Filament\Tables\Filters\SelectFilter::make('feed_warehouse_id')
                    ->label('المستودع')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
