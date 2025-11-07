<?php

namespace App\Filament\Resources\EmployeeAdvances\Schemas;

use App\Enums\AdvanceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeAdvanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('advance_number')
                    ->label('رقم السلفة')
                    ->required(),
                Select::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->required(),
                DatePicker::make('request_date')
                    ->label('تاريخ الطلب')
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                Textarea::make('reason')
                    ->label('السبب')
                    ->columnSpanFull(),
                TextInput::make('approval_status')
                    ->label('حالة الموافقة')
                    ->required()
                    ->default('pending'),
                TextInput::make('approved_by')
                    ->label('وافق بواسطة')
                    ->numeric(),
                DatePicker::make('approved_date')
                    ->label('تاريخ الموافقة'),
                DatePicker::make('disbursement_date')
                    ->label('تاريخ الصرف'),
                TextInput::make('installments_count')
                    ->label('عدد الأقساط')
                    ->numeric(),
                TextInput::make('installment_amount')
                    ->label('مبلغ القسط')
                    ->numeric(),
                TextInput::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->label('الحالة')
                    ->options(AdvanceStatus::class)
                    ->default('active')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
