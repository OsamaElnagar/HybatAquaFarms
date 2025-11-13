<?php

namespace App\Filament\Resources\SalaryRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalaryRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('advanceRepayments')
                ->withSum('advanceRepayments', 'amount'),
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pay_period_start')
                    ->label('بداية الفترة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('pay_period_end')
                    ->label('نهاية الفترة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي الأساسي')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('bonuses')
                    ->label('المكافآت')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي المكافآت')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('deductions')
                    ->label('الخصومات')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي الخصومات')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('advances_deducted')
                    ->label('السُلف المخصومة')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('advance_repayments_sum_amount')
                    ->label('سداد السُلف')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('advance_repayments_count')
                    ->label('عدد سداد السُلف')
                    ->counts('advanceRepayments')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('net_salary')
                    ->label('صافي المرتب')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي الصافي')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'مدفوع',
                        'pending' => 'قيد الانتظار',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_reference')
                    ->label('رقم المرجع')
                    ->searchable()
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
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                    ])
                    ->native(false),
                Filter::make('pay_period')
                    ->label('الفترة')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('من')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('إلى')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('pay_period_start', '>=', $date))
                        ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('pay_period_end', '<=', $date)),
                    ),
            ])
            ->defaultSort('pay_period_start', 'desc')
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
