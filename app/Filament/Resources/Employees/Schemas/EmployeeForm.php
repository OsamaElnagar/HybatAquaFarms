<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_number')
                    ->label('رقم الموظف')
                    ->required(),
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel(),
                TextInput::make('phone2')
                    ->label('هاتف بديل')
                    ->tel(),
                TextInput::make('national_id')
                    ->label('الرقم القومي'),
                Textarea::make('address')
                    ->label('العنوان')
                    ->columnSpanFull(),
                DatePicker::make('hire_date')
                    ->label('تاريخ التوظيف'),
                DatePicker::make('termination_date')
                    ->label('تاريخ إنهاء الخدمة'),
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                TextInput::make('salary_amount')
                    ->label('المرتب')
                    ->numeric(),
                TextInput::make('status')
                    ->label('الحالة')
                    ->required()
                    ->default('active'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
