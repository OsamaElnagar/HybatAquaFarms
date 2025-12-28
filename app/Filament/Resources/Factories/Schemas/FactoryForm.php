<?php

namespace App\Filament\Resources\Factories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FactoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الكود')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('يتم توليده تلقائياً'),
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->helperText('أدخل الاسم الكامل للمصنع'),
                TextInput::make('contact_person')
                    ->label('الشخص المسؤول')
                    ->helperText('اسم الشخص المسؤول عن التواصل مع المصنع'),
                TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel()
                    ->helperText('رقم هاتف أساسي للتواصل مع المصنع'),
                TextInput::make('phone2')
                    ->label('هاتف بديل')
                    ->tel()
                    ->helperText('رقم هاتف إضافي للتواصل في حالة عدم التمكن من الوصول للهاتف الأساسي'),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->helperText('البريد الإلكتروني الرسمي للمصنع'),
                Textarea::make('address')
                    ->label('العنوان')
                    ->helperText('العنوان التفصيلي للمصنع بما في ذلك المدينة والمنطقة')
                    ->columnSpanFull(),
                TextInput::make('payment_terms_days')
                    ->label('أيام الدفع')
                    ->numeric()
                    ->helperText('عدد الأيام التي يجب على العميل الدفع للمنتجات/الخدمات التي يشترها من المصنع'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required()
                    ->helperText('تفعيل/تعطيل المصنع من النظام'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->helperText('أي معلومات إضافية أو ملاحظات خاصة بالمصنع')
                    ->columnSpanFull(),
            ]);
    }
}
