<?php

namespace App\Filament\Resources\PettyCashes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PettyCashForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('المزرعة التي تنتمي إليها العهدة')
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('اسم العهدة')
                            ->required()
                            ->maxLength(255)
                            ->helperText('اسم العهدة (مثل: عهدة المزرعة الرئيسية)')
                            ->columnSpan(1),
                        Select::make('custodian_employee_id')
                            ->label('المستأمن')
                            ->relationship('custodian', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('الموظف المسؤول عن العهدة')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الرصيد الافتتاحي')
                    ->schema([
                        TextInput::make('opening_balance')
                            ->label('الرصيد الافتتاحي')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->prefix('ج.م')
                            ->helperText('الرصيد الأولي عند إنشاء العهدة')
                            ->columnSpan(1),
                        DatePicker::make('opening_date')
                            ->label('تاريخ الافتتاح')
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ بدء العمل بالعهدة')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('هل العهدة نشطة حالياً؟')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('ملاحظات')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية حول العهدة')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
