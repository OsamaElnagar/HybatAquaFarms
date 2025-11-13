<?php

namespace App\Filament\Resources\EmployeeAdvances\Tables;

use App\Enums\AdvanceStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('repayments')
                ->withSum('repayments', 'amount_paid'),
            )
            ->columns([
                TextColumn::make('advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('مبلغ السلفة')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('primary')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي السلف')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('approval_status')
                    ->label('حالة الموافقة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'approved' => 'موافق',
                        'pending' => 'قيد الانتظار',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة الحالية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AdvanceStatus ? $state->getLabel() : $state)
                    ->color(fn ($state) => $state instanceof AdvanceStatus ? $state->getColor() : null)
                    ->sortable(),
                TextColumn::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color(fn (float $state) => $state > 0 ? 'warning' : 'success')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('المتبقي')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('repayments_sum_amount')
                    ->label('إجمالي السداد')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('repayments_count')
                    ->label('عدد عمليات السداد')
                    ->counts('repayments')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),
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
                    ->searchable()
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
                SelectFilter::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('approval_status')
                    ->label('حالة الموافقة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'approved' => 'موافق',
                        'rejected' => 'مرفوض',
                    ])
                    ->native(false),
                SelectFilter::make('status')
                    ->label('الحالة الحالية')
                    ->options(AdvanceStatus::class)
                    ->native(false),
                Filter::make('request_date')
                    ->label('تاريخ الطلب')
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
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('request_date', '>=', $date))
                        ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('request_date', '<=', $date)),
                    ),
            ])
            ->defaultSort('request_date', 'desc')
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
