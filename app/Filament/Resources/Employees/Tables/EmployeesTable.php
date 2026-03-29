<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatus;
use App\Filament\Resources\EmployeeAdvances\Actions\SettleWithExpensesAction;
use App\Filament\Resources\Employees\Actions\MarkDaysOffAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
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
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('advances_count')
                    ->counts('advances')
                    ->label('السلف')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('outstanding_advances')
                    ->label('سلف مستحقة')
                    ->state(fn ($record) => number_format($record->total_outstanding_advances))
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->total_outstanding_advances > 0 ? 'warning' : 'success')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                ActionGroup::make([
                    Action::make('call')
                        ->label('اتصال')
                        ->icon('heroicon-m-phone')
                        ->url(fn ($record) => $record->phone ? 'tel:'.$record->phone : null)
                        ->hidden(fn ($record) => blank($record->phone)),
                    Action::make('whatsapp')
                        ->label('واتساب')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->color('success')
                        ->url(function ($record) {
                            if (blank($record->phone)) {
                                return null;
                            }

                            $phone = preg_replace('/\D+/', '', $record->phone);

                            if (! str_starts_with($phone, '2')) {
                                $phone = '2'.$phone;
                            }

                            return 'https://wa.me/'.$phone;
                        })
                        ->openUrlInNewTab()
                        ->hidden(fn ($record) => blank($record->phone)),
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل'),
                    MarkDaysOffAction::make(),
                    SettleWithExpensesAction::make(),
                ])->label('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
