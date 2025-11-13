<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\SalaryRecords\Schemas\SalaryRecordForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalaryRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryRecords';

    protected static ?string $title = 'سجلات المرتبات';

    public function form(Schema $schema): Schema
    {
        return SalaryRecordForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pay_period_start')
            ->columns([
                TextColumn::make('pay_period_start')
                    ->label('بداية الفترة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('pay_period_end')
                    ->label('نهاية الفترة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->label('المرتب الأساسي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable(),
                TextColumn::make('bonuses')
                    ->label('الحوافز')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->toggleable(),
                TextColumn::make('deductions')
                    ->label('الخصومات')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('danger')
                    ->toggleable(),
                TextColumn::make('advances_deducted')
                    ->label('خصم السلف')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('warning')
                    ->toggleable(),
                TextColumn::make('net_salary')
                    ->label('الصافي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
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
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('من تاريخ'),
                        DatePicker::make('to')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn(Builder $query, $date) => $query->where('pay_period_start', '>=', $date))
                            ->when($data['to'], fn(Builder $query, $date) => $query->where('pay_period_end', '<=', $date));
                    }),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(['pending' => 'معلق', 'paid' => 'مدفوع']),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة سجل مرتب')
                    ->mountUsing(function ($form): void {
                        $form->fill([
                            'employee_id' => $this->getOwnerRecord()->id,
                        ]);
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $data['employee_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('pay_period_start', 'desc');
    }
}
