<?php

namespace App\Filament\Resources\AdvanceRepayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvanceRepaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employeeAdvance.advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employeeAdvance.employee.name_arabic')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('تاريخ السداد')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة السداد')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salaryRecord.id')
                    ->label('رقم سجل المرتب')
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
