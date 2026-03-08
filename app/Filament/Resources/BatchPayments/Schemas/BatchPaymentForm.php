<?php

namespace App\Filament\Resources\BatchPayments\Schemas;

use App\Enums\PaymentMethod;
use App\Models\BatchFish;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

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
                            ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('batch_fish_id', null))
                            ->helperText('اختر دفعة الزريعة التي تم شراؤها من المورد')
                            ->columnSpan(1),
                        Select::make('batch_fish_id')
                            ->label('الصنف والمورد')
                            ->options(function (Get $get) {
                                $batchId = $get('batch_id');
                                if (! $batchId) {
                                    return [];
                                }

                                return BatchFish::with(['species', 'factory'])
                                    ->where('batch_id', $batchId)
                                    ->get()
                                    ->mapWithKeys(function ($fish) {
                                        $factoryName = $fish->factory ? $fish->factory->name : 'بدون مورد';

                                        return [$fish->id => "{$factoryName} - {$fish->species->name} - عدد {$fish->quantity} - {$fish->total_cost} EGP"];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $fish = BatchFish::find($state);
                                    if ($fish && $fish->factory_id) {
                                        $set('factory_id', $fish->factory_id);
                                    }
                                }
                            })
                            ->helperText('اختر الصنف والمورد المرتبط بهذه الدفعة')
                            ->columnSpan(1),
                        \Filament\Forms\Components\Hidden::make('factory_id')
                            ->live(),
                        TextEntry::make('factory_balance')
                            ->label('رصيد المورد (لجميع الزريعة)')
                            ->state(function (Get $get) {
                                $factoryId = $get('factory_id');
                                if (! $factoryId) {
                                    return '-';
                                }

                                $factory = \App\Models\Factory::find($factoryId);
                                if (! $factory) {
                                    return '-';
                                }

                                $balance = $factory->outstanding_balance;
                                $color = $balance > 0 ? 'text-danger-600' : 'text-success-600';

                                return new \Illuminate\Support\HtmlString(
                                    "<span class='font-bold {$color}'>".number_format($balance).' EGP</span>'
                                );
                            })
                            ->columnSpanFull(),
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
