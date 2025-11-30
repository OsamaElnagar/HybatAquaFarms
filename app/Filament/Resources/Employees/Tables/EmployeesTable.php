<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label('رقم الموظف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('national_id')
                    ->label('الرقم القومي')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hire_date')
                    ->label('تاريخ التوظيف')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('basic_salary')
                    ->label('المرتب')
                    ->numeric()
                    ->suffix(' EGP ')
                    ->sortable(),
                TextColumn::make('advances_count')
                    ->counts('advances')
                    ->label('السلف')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('outstanding_advances')
                    ->label('سلف مستحقة')
                    ->state(fn ($record) => number_format($record->total_outstanding_advances))
                    ->suffix(' EGP ')
                    ->color(fn ($record) => $record->total_outstanding_advances > 0 ? 'warning' : 'success')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                // ->toggleable(),
                TextColumn::make('termination_date')
                    ->label('تاريخ إنهاء الخدمة')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(EmployeeStatus::class),
                TernaryFilter::make('hire_date')
                    ->label('تاريخ التوظيف'),
                TernaryFilter::make('termination_date')
                    ->label('تاريخ إنهاء الخدمة'),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
