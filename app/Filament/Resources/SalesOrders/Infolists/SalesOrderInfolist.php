<?php

namespace App\Filament\Resources\SalesOrders\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('رقم العملية')
                            ->columnSpan(1),
                        TextEntry::make('date')
                            ->label('التاريخ')
                            ->date('Y-m-d')
                            ->columnSpan(1),
                        TextEntry::make('farm.name')
                            ->label('المزرعة')
                            ->columnSpan(1),
                        TextEntry::make('trader.name')
                            ->label('التاجر')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المبالغ')
                    ->schema([
                        TextEntry::make('boxes_subtotal')
                            ->label('المجموع الفرعي')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' ج.م')
                            ->columnSpan(1),
                        TextEntry::make('tax_amount')
                            ->label('الضرائب')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' ج.م')
                            ->columnSpan(1),
                        TextEntry::make('discount_amount')
                            ->label('الخصم')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' ج.م')
                            ->color('warning')
                            ->columnSpan(1),
                        TextEntry::make('net_amount')
                            ->label('المجموع الإجمالي')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' ج.م')
                            ->color('success')
                            ->weight('bold')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الحالة والتوصيل')
                    ->schema([
                        TextEntry::make('payment_status')
                            ->label('حالة الدفع')
                            ->badge()
                            ->columnSpan(1),
                        TextEntry::make('delivery_status')
                            ->label('حالة التوصيل')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending' => 'معلق',
                                'delivered' => 'تم التوصيل',
                                'cancelled' => 'ملغي',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'pending' => 'warning',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(1),
                        TextEntry::make('delivery_date')
                            ->label('تاريخ التوصيل')
                            ->date('Y-m-d')
                            ->placeholder('لم يتم التوصيل بعد')
                            ->columnSpan(1),
                        TextEntry::make('createdBy.name')
                            ->label('أنشأ بواسطة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الإحصائيات')
                    ->schema([
                        TextEntry::make('items_count')
                            ->label('عدد الأصناف')
                            ->state(fn ($record) => $record->total_items)
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('total_quantity')
                            ->label('إجمالي الكمية')
                            ->state(fn ($record) => number_format($record->total_quantity))
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('harvests_count')
                            ->label('سجلات الحصاد')
                            ->state(fn ($record) => $record->harvests()->count())
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('عنوان التوصيل والملاحظات')
                    ->schema([
                        TextEntry::make('delivery_address')
                            ->label('عنوان التوصيل')
                            ->placeholder('غير محدد')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->delivery_address) && empty($record->notes)),
            ]);
    }
}
