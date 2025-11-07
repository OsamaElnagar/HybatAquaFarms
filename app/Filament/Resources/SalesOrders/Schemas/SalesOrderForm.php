<?php

namespace App\Filament\Resources\SalesOrders\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('رقم الأمر')
                    ->required(),
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                Select::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->required(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                TextInput::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->required()
                    ->numeric(),
                TextInput::make('tax_amount')
                    ->label('الضرائب')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->label('الخصم')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->label('المجموع الكلي')
                    ->required()
                    ->numeric(),
                Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(PaymentStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('delivery_status')
                    ->label('حالة التسليم'),
                DatePicker::make('delivery_date')
                    ->label('تاريخ التسليم'),
                Textarea::make('delivery_address')
                    ->label('عنوان التسليم')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->label('أنشأ بواسطة')
                    ->numeric(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
