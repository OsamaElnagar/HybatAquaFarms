<?php

namespace App\Filament\Resources\SalaryRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalaryRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->required(),
                DatePicker::make('pay_period_start')
                    ->label('بداية الفترة')
                    ->required()
                    ->default(now()->startOfMonth()),
                DatePicker::make('pay_period_end')
                    ->label('نهاية الفترة')
                    ->required()
                    ->default(now()->endOfMonth()),
                TextInput::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('bonuses')
                    ->label('المكافآت')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->default(0)
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('deductions')
                    ->label('الخصومات')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->default(0)
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('advances_deducted')
                    ->label('السُلف المخصومة')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->default(0)
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('net_salary')
                    ->label('صافي المرتب')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->step(0.01)
                    ->helperText('الراتب الأساسي + المكافآت - الخصومات - السُلف'),
                DatePicker::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->default(now()),
                TextInput::make('payment_method')
                    ->label('طريقة الدفع')
                    ->maxLength(50)
                    ->placeholder('نقدي، تحويل بنكي، شيك...'),
                TextInput::make('payment_reference')
                    ->label('رقم المرجع')
                    ->maxLength(100)
                    ->placeholder('رقم المرجع من البنك أو السجل'),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull()
                    ->maxLength(1000),
            ]);
    }
}
