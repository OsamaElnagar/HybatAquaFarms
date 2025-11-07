<?php

namespace App\Filament\Resources\EmployeeAdvances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name_arabic')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable(),
                TextColumn::make('approval_status')
                    ->label('حالة الموافقة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved' => 'موافق',
                        'pending' => 'قيد الانتظار',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('installments_count')
                    ->label('عدد الأقساط')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('installment_amount')
                    ->label('مبلغ القسط')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('approved_date')
                    ->label('تاريخ الموافقة')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('disbursement_date')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('approvedBy.name')
                    ->label('وافق بواسطة')
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
