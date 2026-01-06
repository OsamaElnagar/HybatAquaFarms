<?php

namespace App\Filament\Resources\FeedMovements\Tables;

use App\Enums\FeedMovementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_type')
                    ->label('نوع الحركة')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromWarehouse.name')
                    ->label('من المستودع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('toWarehouse.name')
                    ->label('إلى المستودع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('المصنع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
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
                SelectFilter::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(FeedMovementType::class)
                    ->native(false),
                SelectFilter::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('from_warehouse_id')
                    ->label('من المستودع')
                    ->relationship('fromWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('to_warehouse_id')
                    ->label('إلى المستودع')
                    ->relationship('toWarehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn ($record) => $record->movement_type !== \App\Enums\FeedMovementType::Out)
                    ->tooltip(fn ($record) => $record->movement_type === \App\Enums\FeedMovementType::Out
                        ? 'لا يمكن تعديل حركات الصرف - يتم إنشاؤها تلقائياً من الصرف اليومي'
                        : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
