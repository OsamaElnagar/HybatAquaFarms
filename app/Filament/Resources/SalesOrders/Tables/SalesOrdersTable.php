<?php

namespace App\Filament\Resources\SalesOrders\Tables;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Farms\RelationManagers\SalesOrdersRelationManager as FSORM;
use App\Filament\Resources\Traders\RelationManagers\SalesOrdersRelationManager as TSORM;
use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['harvestOperation.batch', 'orders']))
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم العملية')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable()
                    ->hiddenOn(TSORM::class),
                TextColumn::make('net_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->color('primary')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->wrap()
                    ->description(fn (Model $record): string => $record->orders->map(fn ($o) => "{$o->code}")->implode(' '))
                    ->sortable(),
                TextColumn::make('harvestOperation.operation_number')
                    ->label('عملية الحصاد')
                    ->description(fn (Model $record): string => $record->harvestOperation?->batch?->batch_code)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable()
                    ->toggleable()
                    ->hiddenOn(FSORM::class),
                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivery_date')
                    ->label('تاريخ التوصيل')
                    ->date('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name'),
                SelectFilter::make('harvest_operation_id')
                    ->label('عملية الحصاد')
                    ->relationship('harvestOperation', 'operation_number'),
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                DateRangeFilter::make('date'),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(PaymentStatus::class)
                    ->native(false),
                SelectFilter::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->options(DeliveryStatus::class)
                    ->native(false),
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
