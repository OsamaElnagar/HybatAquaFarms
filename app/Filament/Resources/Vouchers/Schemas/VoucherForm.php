<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Enums\VoucherType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                Select::make('voucher_type')
                    ->label('نوع السند')
                    ->options(VoucherType::class)
                    ->required(),
                TextInput::make('voucher_number')
                    ->label('رقم السند')
                    ->required(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                TextInput::make('counterparty_type')
                    ->label('نوع الطرف المقابل')
                    ->required(),
                TextInput::make('counterparty_id')
                    ->label('الطرف المقابل')
                    ->required()
                    ->numeric(),
                Select::make('petty_cash_id')
                    ->label('العهدة')
                    ->relationship('pettyCash', 'name'),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                TextInput::make('payment_method')
                    ->label('طريقة الدفع'),
                TextInput::make('reference_number')
                    ->label('رقم المرجع'),
                TextInput::make('created_by')
                    ->label('أنشأ بواسطة')
                    ->numeric(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
