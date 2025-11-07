<?php

namespace App\Filament\Resources\ClearingEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClearingEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('factory.name')
                    ->label('المصنع')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('journalEntry.entry_number')
                    ->label('رقم القيد')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('date', 'desc')
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
