<?php

namespace App\Filament\Resources\Traders\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TraderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('code')
                            ->label('الكود')
                            ->columnSpan(1),
                        TextEntry::make('name')
                            ->label('الاسم')
                            ->columnSpan(1),
                        TextEntry::make('contact_person')
                            ->label('الشخص المسؤول')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('trader_type')
                            ->label('نوع التاجر')
                            ->badge()
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('معلومات الاتصال')
                    ->schema([
                        TextEntry::make('phone')
                            ->label('الهاتف')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('phone2')
                            ->label('هاتف بديل')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('is_active')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'نشط' : 'غير نشط')
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الشروط المالية')
                    ->schema([
                        TextEntry::make('payment_terms_days')
                            ->label('أيام الدفع')
                            ->formatStateUsing(fn ($state) => $state ? $state.' يوم' : 'غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('credit_limit')
                            ->label('حد الائتمان')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' ج.م' : 'غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الإحصائيات المالية')
                    ->schema([
                        TextEntry::make('sales_orders_count')
                            ->label('عدد المبيعات')
                            ->state(fn ($record) => $record->salesOrders()->count())
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('total_sales')
                            ->label('إجمالي المبيعات')
                            ->state(fn ($record) => number_format($record->salesOrders()->sum('total_amount'), 2).' ج.م')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('outstanding_balance')
                            ->label('الرصيد المستحق')
                            ->state(fn ($record) => number_format($record->outstanding_balance, 2).' ج.م')
                            ->badge()
                            ->color(fn ($record) => $record->outstanding_balance > 0 ? 'warning' : 'success')
                            ->columnSpan(1),
                        TextEntry::make('clearing_entries_count')
                            ->label('التسويات')
                            ->state(fn ($record) => $record->clearingEntries()->count())
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('العنوان والملاحظات')
                    ->schema([
                        TextEntry::make('address')
                            ->label('العنوان')
                            ->placeholder('غير محدد')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->address) && empty($record->notes)),
            ]);
    }
}
