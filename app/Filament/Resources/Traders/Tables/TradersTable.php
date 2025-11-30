<?php

namespace App\Filament\Resources\Traders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TradersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->label('الشخص المسؤول')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                TextColumn::make('trader_type')
                    ->label('النوع')
                    ->badge()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sales_orders_count')
                    ->counts('salesOrders')
                    ->label('أوامر البيع')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('outstanding_balance')
                    ->label('المستحقات')
                    ->state(fn ($record) => number_format($record->outstanding_balance))
                    ->suffix(' EGP ')
                    ->color(fn ($record) => $record->outstanding_balance > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('credit_limit')
                    ->label('حد الائتمان')
                    ->numeric()
                    ->suffix(' EGP ')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trader_type')
                    ->label('نوع التاجر')
                    ->options([
                        'wholesale' => 'جملة',
                        'retail' => 'تجزئة',
                        'exporter' => 'مصدّر',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('نشط'),
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
            ->defaultSort('name');
    }
}
