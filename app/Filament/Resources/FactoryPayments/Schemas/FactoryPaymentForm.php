<?php

namespace App\Filament\Resources\FactoryPayments\Schemas;

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use App\Models\Factory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FactoryPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدفعة')
                    ->description('يرجى إدخال بيانات الدفعة')
                    ->schema([
                        Select::make('factory_id')
                            ->label('المصنع / المورد')
                            ->relationship('factory', 'name')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر المصنع أو المورد الذي تم الدفع له'),
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('factory_id') && Factory::find($get('factory_id'))?->type === FactoryType::SUPPLIER)
                            ->visible(fn (Get $get) => $get('factory_id') && Factory::find($get('factory_id'))?->type === FactoryType::SUPPLIER)
                            ->helperText('المزرعة المرتبطة بهذه الدفعة'),
                        DatePicker::make('date')
                            ->label('تاريخ الدفعة')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required()
                            ->default(now())
                            ->helperText('تاريخ عملية الدفع'),
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->suffix(' EGP ')
                            ->helperText('أدخل مبلغ الدفعة بالجنيه المصري'),
                        Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options(PaymentMethod::class)
                            ->searchable()
                            ->helperText('اختر طريقة الدفع المستخدمة'),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع')
                            ->maxLength(255)
                            ->helperText('رقم المرجع للدفعة (مثل رقم الشيك أو رقم التحويل)'),
                    ])
                    ->columns(2),

                Section::make('تفاصيل إضافية')
                    ->description('معلومات إضافية عن الدفعة')
                    ->schema([
                        Textarea::make('description')
                            ->label('الوصف')
                            ->columnSpanFull()
                            ->maxLength(500)
                            ->helperText('وصف الدفعة أو سبب الدفع'),
                        Select::make('recorded_by')
                            ->label('سجل بواسطة')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth('web')->id())
                            ->helperText('المستخدم الذي قام بتسجيل الدفعة'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->helperText('أية ملاحظات إضافية متعلقة بالدفعة'),
                    ])
                    ->columns(1),
            ]);
    }
}
