<?php

namespace App\Filament\Resources\AdvanceRepayments\Tables;

use App\Enums\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdvanceRepaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['employeeAdvance.employee', 'salaryRecord']))
            ->columns([
                TextColumn::make('employeeAdvance.advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employeeAdvance.employee.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state) => $state ?? 'الموظف غير موجود'),
                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي المبلغ المدفوع')
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                TextColumn::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn (float $state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('employeeAdvance.balance_remaining')
                    ->label('الرصيد المتبقي للسلفة')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salaryRecord.id')
                    ->label('رقم السجل المرتب')
                    ->formatStateUsing(fn ($state) => $state ? '#'.$state : 'السجل غير موجود')
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
                SelectFilter::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employeeAdvance.employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('employee_advance_id')
                    ->label('رقم السلفة')
                    ->relationship('employeeAdvance', 'advance_number')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->native(false),
                Filter::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from')->label('من')->displayFormat('Y-m-d')->native(false),
                        \Filament\Forms\Components\DatePicker::make('to')->label('إلى')->displayFormat('Y-m-d')->native(false),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '<=', $date)),
                    ),
            ])
            ->defaultSort('payment_date', 'desc')
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
