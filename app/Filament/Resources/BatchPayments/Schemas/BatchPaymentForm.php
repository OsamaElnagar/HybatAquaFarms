<?php

namespace App\Filament\Resources\BatchPayments\Schemas;

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BatchPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدفعة والطرف')
                    ->description('يرجى اختيار دفعة الزريعة والمورد المرتبط بها')
                    ->schema([
                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: fn ($query) => $query->latest())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر دفعة الزريعة التي تم شراؤها من المورد')
                            ->columnSpan(1),
                        Select::make('factory_id')
                            ->label('المورد (مصنع التفريخ)')
                            ->relationship('factory', 'name', function (Builder $query) {
                                return $query->where('type', FactoryType::SEEDS);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('المورد الذي تم شراء الزريعة منه - سيتم استخدامه لتتبع المدفوعات')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('تفاصيل الدفعة')
                    ->description('معلومات الدفعة المالية')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date')
                                    ->label('تاريخ الدفعة')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('Y-m-d')
                                    ->native(false)
                                    ->helperText('تاريخ إجراء الدفعة')
                                    ->columnSpan(1),
                                TextInput::make('amount')
                                    ->label('المبلغ')
                                    ->required()
                                    ->numeric()
                                    ->suffix(' EGP ')
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->helperText('المبلغ المدفوع بالجنيه المصري')
                                    ->columnSpan(1),
                                Select::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->options(PaymentMethod::class)
                                    ->searchable()
                                    ->helperText('اختر طريقة الدفع المستخدمة')
                                    ->columnSpan(1),
                            ]),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع')
                            ->maxLength(255)
                            ->helperText('رقم المرجع أو رقم الشيك أو رقم التحويل البنكي (إن وجد)')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('وصف الدفعة')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('وصف تفصيلي للدفعة (اختياري) - مثال: "دفعة جزئية للزريعة - دفعة BATCH-2024-001"')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('معلومات إضافية')
                    ->description('معلومات المستخدم والملاحظات')
                    ->schema([
                        Select::make('recorded_by')
                            ->label('سجل بواسطة')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth('web')->id())
                            ->helperText('المستخدم الذي قام بتسجيل هذه الدفعة في النظام')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->maxLength(1000)
                            ->rows(4)
                            ->helperText('أي ملاحظات إضافية متعلقة بهذه الدفعة (اختياري)')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
