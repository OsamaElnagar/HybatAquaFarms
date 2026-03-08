<?php

namespace App\Filament\Resources\SalesOrders\Schemas;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                            ->hidden()
                            ->columnSpan(1),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ إستلام الفاتورة')
                            ->columnSpan(1),
                        Select::make('harvest_operation_id')
                            ->label('عملية الحصاد')
                            ->relationship(
                                'harvestOperation',
                                'operation_number',
                                modifyQueryUsing: fn ($query) => $query->with('farm')->latest()
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_display_name)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('عملية الحصاد المصدره للمنتجات')
                            ->columnSpan(1),
                        Select::make('trader_id')
                            ->label('التاجر (الحلقة)')
                            ->relationship('trader', 'name', function (Builder $query, $get) {
                                $hopId = $get('harvest_operation_id');

                                if ($hopId) {
                                    $query->whereHas('orders', function ($orderQuery) use ($hopId) {
                                        $orderQuery->where('harvest_operation_id', $hopId);
                                    });
                                }
                            })
                            ->required()
                            ->visible(fn (Get $get): bool => filled($get('harvest_operation_id')))
                            ->searchable()
                            ->preload()
                            ->helperText('التاجر أو الحلقة المشتريه')
                            ->live()
                            ->columnSpan(1),
                        CheckboxList::make('orders')
                            ->label('الطلبات')
                            ->relationship('orders', 'code', modifyQueryUsing: function (Builder $query, $get, ?Model $record) {
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

                                // Exclude orders already assigned to any other sales order
                                $query->whereDoesntHave('salesOrders', function ($q) use ($record) {
                                    if ($record && $record->exists) {
                                        $q->where('sales_orders.id', '!=', $record->id);
                                    }
                                });

                                // Eager load for label generation
                                $query->with(['trader', 'harvest']);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->code} - {$record->trader?->name} - جلسة رقم {$record->harvest?->harvest_number}")
                            ->selectAllAction(
                                fn (Action $action) => $action->label('اختيار جميع الطلبات'),
                            )
                            ->searchable()
                            ->noSearchResultsMessage('لا توجد نتائج للبحث')
                            ->searchPrompt('ابحث عن كود الطلب...')
                            ->searchDebounce(500)
                            ->visible(fn (Get $get) => $get('harvest_operation_id') && $get('trader_id'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المبالغ')
                    ->schema([
                        TextInput::make('boxes_subtotal')
                            ->label('المجموع الفرعي')
                            // ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المجموع قبل الضرائب والخصم')
                            ->columnSpan(1)
                            ->visible(false),
                        TextInput::make('tax_amount')
                            ->label('الضرائب')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('قيمة الضرائب (إن وُجدت)')
                            ->columnSpan(1)
                            ->visible(false),
                        TextInput::make('discount_amount')
                            ->label('الخصم')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('قيمة الخصم (إن وُجد)')
                            ->columnSpan(1)
                            ->visible(false),
                        TextInput::make('net_amount')
                            ->label('المجموع الإجمالي')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المبلغ النهائي بعد الضرائب والخصم')
                            ->columnSpan(1),
                        Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options(PaymentStatus::class)
                            ->default(PaymentStatus::Paid)
                            ->hidden(false)
                            ->native(false)
                            ->helperText('حالة دفع عملية البيع')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                // Section::make('الحالة والتوصيل')
                //     ->schema([
                //         Select::make('payment_status')
                //             ->label('حالة الدفع')
                //             ->options(PaymentStatus::class)
                //             ->default(PaymentStatus::Paid)
                //             ->hidden()
                //             ->native(false)
                //             ->helperText('حالة دفع عملية البيع')
                //             ->columnSpan(1),
                //         // Select::make('delivery_status')
                //         //     ->label('حالة التوصيل')
                //         //     ->options(DeliveryStatus::class)
                //         //     ->native(false)
                //         //     ->helperText('حالة توصيل المنتجات')
                //         //     ->columnSpan(1),
                //         // DatePicker::make('delivery_date')
                //         //     ->label('تاريخ التوصيل')
                //         //     ->displayFormat('Y-m-d')
                //         //     ->native(false)
                //         //     ->helperText('تاريخ توصيل المنتجات للعميل')
                //         //     ->columnSpan(1),

                //     ])
                //     ->columns(2)
                //     ->columnSpanFull()
                //     ->collapsible(),

                Section::make('الملاحظات')
                    ->schema([

                        Hidden::make('created_by')
                            ->label('أنشأ بواسطة')
                            ->default(fn () => Auth::id())
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية على العملية'),
                    ])
                    ->columns(1)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
