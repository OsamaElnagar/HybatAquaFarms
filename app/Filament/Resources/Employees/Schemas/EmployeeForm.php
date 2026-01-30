<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmployeeStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextInput::make('employee_number')
                            ->label('رقم الموظف')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يتم توليده تلقائياً')
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255)
                            ->helperText('الاسم الكامل للموظف')
                            ->columnSpan(1),
                        TextInput::make('phone')
                            ->label('الهاتف')
                            ->tel()
                            ->maxLength(255)
                            ->helperText('رقم الهاتف الأساسي')
                            ->columnSpan(1),
                        TextInput::make('phone2')
                            ->label('هاتف بديل')
                            ->tel()
                            ->maxLength(255)
                            ->helperText('رقم هاتف احتياطي (اختياري)')
                            ->columnSpan(1),
                        TextInput::make('national_id')
                            ->label('الرقم القومي')
                            ->maxLength(255)
                            ->helperText('الرقم القومي أو رقم البطاقة')
                            ->columnSpan(1),
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                            ->disabled(fn ($livewire) => $livewire instanceof RelationManager)
                            ->searchable()
                            ->preload()
                            ->helperText('المزرعة التي يعمل بها الموظف')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('التوظيف والراتب')
                    ->schema([
                        DatePicker::make('hire_date')
                            ->label('تاريخ التوظيف')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ بدء العمل')
                            ->columnSpan(1),
                        DatePicker::make('termination_date')
                            ->label('تاريخ إنهاء الخدمة')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ ترك العمل (إن وُجد)')
                            ->columnSpan(1),
                        TextInput::make('basic_salary')
                            ->label('المرتب الشهري')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المرتب الشهري الأساسي')
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(EmployeeStatus::class)
                            ->required()
                            ->default(EmployeeStatus::ACTIVE)
                            ->native(false)
                            ->helperText('حالة الموظف الحالية')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('العنوان والملاحظات')
                    ->schema([
                        Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('عنوان إقامة الموظف')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
