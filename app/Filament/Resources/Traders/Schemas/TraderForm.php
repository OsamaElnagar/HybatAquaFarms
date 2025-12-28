<?php

namespace App\Filament\Resources\Traders\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TraderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->required()->disabled()
                            ->hidden()
                            ->dehydrated(false),
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255)
                            ->helperText('اسم التاجر أو الشركة')
                            ->columnSpan(1),
                        TextInput::make('contact_person')
                            ->label('الشخص المسؤول')
                            ->maxLength(255)
                            ->helperText('اسم الشخص المسؤول للتواصل')
                            ->columnSpan(1),
                        TextInput::make('trader_type')
                            ->label('نوع التاجر')
                            ->maxLength(255)
                            ->helperText('نوع التاجر (جملة، تجزئة، مصدّر)')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('معلومات الاتصال')
                    ->schema([
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
                            ->helperText('رقم هاتف احتياطي')
                            ->columnSpan(1),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255)
                            ->helperText('البريد الإلكتروني للتواصل')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('هل التاجر نشط حالياً؟')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('الشروط المالية')
                    ->schema([
                        TextInput::make('payment_terms_days')
                            ->label('أيام الدفع')
                            ->numeric()
                            ->minValue(0)
                            ->suffix(' يوم')
                            ->helperText('عدد أيام السماح للدفع')
                            ->columnSpan(1),
                        TextInput::make('credit_limit')
                            ->label('حد الائتمان')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('الحد الأقصى للائتمان المسموح')
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
                            ->helperText('عنوان التاجر أو الشركة')
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
