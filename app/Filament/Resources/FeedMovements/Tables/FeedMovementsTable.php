<?php

namespace App\Filament\Resources\FeedMovements\Tables;

use App\Enums\FactoryType;
use App\Enums\FeedMovementType;
use App\Filament\Resources\FeedWarehouses\FeedWarehouseResource;
use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
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
                    ->description(fn ($record) => $record->movement_type === FeedMovementType::Sale ? $record->buyer_name : null)
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromWarehouse.name')
                    ->label('من المستودع')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->from_warehouse_id ? FeedWarehouseResource::getUrl('edit', ['record' => $record->from_warehouse_id]) : null)
                    ->color(fn ($record) => $record->from_warehouse_id ? 'primary' : null)
                    ->toggleable(),
                TextColumn::make('toWarehouse.name')
                    ->label('إلى المستودع')
                    ->searchable()
                    ->placeholder(fn ($record) => $record->movement_type === FeedMovementType::Sale ? 'بيع ل '.$record->buyer_name : null)
                    ->sortable()
                    ->url(fn ($record) => $record->to_warehouse_id ? FeedWarehouseResource::getUrl('edit', ['record' => $record->to_warehouse_id]) : null)
                    ->color(fn ($record) => $record->to_warehouse_id ? 'primary' : null)
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->summarize(Sum::make()->numeric(locale: 'en', decimalPlaces: 0)->label('مجموع الكمية'))
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('التكلفة')
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->suffix(' EGP')
                    ->summarize(Sum::make()->numeric(locale: 'en', decimalPlaces: 0)->label('اجمالى التكلفة')->suffix('  EGP'))
                    ->sortable(),
                // ->visible(fn($record) => $record->movement_type === FeedMovementType::In),
                TextColumn::make('buyer_name')
                    ->label('المشتري')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('sale_price')
                    ->label('سعر الشحنة')
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->suffix(' EGP')
                    ->summarize(Sum::make()->numeric(locale: 'en', decimalPlaces: 0)->label('اجمالى البيع')->suffix(' EGP'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('factory.name')
                    ->label('المصنع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name', modifyQueryUsing: function ($query) {
                        return $query->where('type', FactoryType::FEEDS);
                    })
                    ->searchable()
                    ->preload(),
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
                DateRangeFilter::make('date'),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn ($record) => $record->movement_type !== FeedMovementType::Out)
                    ->tooltip(fn ($record) => $record->movement_type === FeedMovementType::Out
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
