<?php

namespace App\Filament\Resources\SalesOrders\Tables;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم العملية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harvestOperation.operation_number')
                    ->label('عملية الحصاد')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable()
                    ->toggleable(),
                // TextColumn::make('items_count')
                //     ->counts('items')
                //     ->label('الأصناف')
                //     ->badge()
                //     ->color('primary')
                //     ->sortable(),
                TextColumn::make('net_amount')
                    ->label('المبلغ الإجمالي')
                    ->numeric(0)
                    ->suffix(' EGP ')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('تاريخ التوصيل')
                    ->date('Y-m-d')
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(PaymentStatus::class)
                    ->native(false),
                SelectFilter::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->options(DeliveryStatus::class)
                    ->native(false),
                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name'),
                SelectFilter::make('harvest_operation_id')
                    ->label('عملية الحصاد')
                    ->relationship('harvestOperation', 'operation_number')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
