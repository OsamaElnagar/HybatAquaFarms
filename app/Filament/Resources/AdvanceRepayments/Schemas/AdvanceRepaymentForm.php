<?php

namespace App\Filament\Resources\AdvanceRepayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdvanceRepaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_advance_id')
                    ->label('السلفة')
                    ->relationship('employeeAdvance', 'advance_number', fn ($query) => $query->where('balance_remaining', '>', 0))
                    ->searchable()
                    ->required()
                    ->helperText('اختر السلفة المراد سدادها')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "رقم {$record->advance_number} - {$record->employee->name_arabic} - متبقي: {$record->balance_remaining} ج.م"),
                DatePicker::make('payment_date')
                    ->label('تاريخ السداد')
                    ->required()
                    ->default(now()),
                TextInput::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0.01)
                    ->step(0.01)
                    ->helperText('المبلغ المراد سداده من هذه السلفة'),
                Select::make('payment_method')
                    ->label('طريقة السداد')
                    ->options([
                        'salary_deduction' => 'خصم من المرتب',
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'check' => 'شيك',
                    ])
                    ->required()
                    ->default('salary_deduction'),
                Select::make('salary_record_id')
                    ->label('سجل المرتب المرتبط')
                    ->relationship('salaryRecord', 'id', fn ($query) => $query->where('status', 'paid'))
                    ->searchable()
                    ->helperText('إذا كان السداد من خلال خصم المرتب، اختر سجل المرتب المرتبط')
                    ->visible(fn ($get) => $get('payment_method') === 'salary_deduction'),
                TextInput::make('balance_remaining')
                    ->label('الرصيد المتبقي بعد السداد')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->default(0)
                    ->minValue(0)
                    ->step(0.01)
                    ->helperText('سيتم حساب الرصيد المتبقي تلقائياً بعد السداد')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull()
                    ->maxLength(1000),
            ]);
    }
}
