<?php

namespace App\Filament\Resources\FactoryPayments\Schemas;

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FactoryPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدفعة')
                    ->description('يرجى إدخال بيانات الدفعة لمصنع الأعلاف')
                    ->schema([
                        Select::make('factory_id')
                            ->label('المصنع')
                            ->relationship('factory', 'name', function (Builder $query) {
                                return $query->where('type', FactoryType::FEEDS);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر مصنع الأعلاف الذي تم الدفع له'),
                        DatePicker::make('date')
                            ->label('تاريخ الدفعة')
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
