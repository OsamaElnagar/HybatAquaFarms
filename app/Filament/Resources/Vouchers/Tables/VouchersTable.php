<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('voucher_number')
                    ->label('رقم السند')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('voucher_type')
                    ->label('نوع السند')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\VoucherType ? $state->getLabel() : $state)
                    ->color(fn ($state) => match ($state instanceof \App\Enums\VoucherType ? $state->value : $state) {
                        'receipt' => 'success',
                        'payment' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('counterparty')
                    ->label('الطرف المقابل')
                    ->formatStateUsing(function ($record) {
                        if (! $record->counterparty) {
                            return '-';
                        }
                        if ($record->counterparty_type === \App\Models\Trader::class) {
                            return $record->counterparty->name;
                        }
                        if ($record->counterparty_type === \App\Models\Employee::class) {
                            return $record->counterparty->name;
                        }
                        if ($record->counterparty_type === \App\Models\Factory::class) {
                            return $record->counterparty->name;
                        }

                        return '-';
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color(fn ($record) => $record->voucher_type?->value === 'receipt' ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('pettyCash.name')
                    ->label('العهدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('reference_number')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
