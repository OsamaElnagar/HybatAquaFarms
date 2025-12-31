<?php

namespace App\Filament\Resources\SalesOrders\Schemas;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات العملية')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('رقم العملية')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يتم توليده تلقائياً')
                            ->columnSpan(1),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ إصدار عملية البيع')
                            ->columnSpan(1),
                        Select::make('harvest_operation_id')
                            ->label('عملية الحصاد')
                            ->relationship('harvestOperation', 'operation_number', modifyQueryUsing: fn ($query) => $query->with('farm'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_display_name)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('عملية الحصاد المصدر للمنتجات')
                            ->columnSpan(1),
                        Select::make('trader_id')
                            ->label('التاجر (العميل)')
                            ->relationship('trader', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('التاجر المشتري')
                            ->live() // Make it live to filter orders maybe?
                            ->columnSpan(1),
                        Select::make('orders')
                            ->label('الطلبات')
                            ->relationship('orders', 'code', modifyQueryUsing: function (Builder $query, $get) {
                                // Filter by trader if selected
                                $traderId = $get('trader_id');
                                if ($traderId) {
                                    $query->where('trader_id', $traderId);
                                }

                                // Filter by harvest operation if selected
                                $hopId = $get('harvest_operation_id');
                                if ($hopId) {
                                    $query->where('harvest_operation_id', $hopId);
                                }
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المبالغ')
                    ->schema([
                        TextInput::make('boxes_subtotal')
                            ->label('المجموع الفرعي')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المجموع قبل الضرائب والخصم')
                            ->columnSpan(1),
                        TextInput::make('tax_amount')
                            ->label('الضرائب')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('قيمة الضرائب (إن وُجدت)')
                            ->columnSpan(1),
                        TextInput::make('discount_amount')
                            ->label('الخصم')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('قيمة الخصم (إن وُجد)')
                            ->columnSpan(1),
                        TextInput::make('net_amount')
                            ->label('المجموع الإجمالي')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المبلغ النهائي بعد الضرائب والخصم')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('الحالة والتوصيل')
                    ->schema([
                        Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options(PaymentStatus::class)

                            ->required()
                            ->native(false)
                            ->helperText('حالة دفع عملية البيع')
                            ->columnSpan(1),
                        Select::make('delivery_status')
                            ->label('حالة التوصيل')
                            ->options(DeliveryStatus::class)
                            ->native(false)
                            ->helperText('حالة توصيل المنتجات')
                            ->columnSpan(1),
                        DatePicker::make('delivery_date')
                            ->label('تاريخ التوصيل')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ توصيل المنتجات للعميل')
                            ->columnSpan(1),
                        Select::make('created_by')
                            ->label('أنشأ بواسطة')
                            ->relationship('createdBy', 'name')
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->preload()
                            ->helperText('المستخدم الذي أنشأ العملية')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('التوصيل والملاحظات')
                    ->schema([
                        Textarea::make('delivery_address')
                            ->label('عنوان التوصيل')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('عنوان توصيل المنتجات')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية على العملية')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
