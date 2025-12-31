<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Enums\PaymentMethod;
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
                Select::make('treasury_account_id')
                    ->label('الخزينة / البنك')
                    ->options(fn($get) => \App\Models\Account::query()
                        ->where('is_treasury', true)
                        ->when($get('farm_id'), fn($q) => $q->where('farm_id', $get('farm_id')))
                        ->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('account_id')
                    ->label('حساب البند (مصروف/إيراد/عميل)')
                    ->options(fn($get) => \App\Models\Account::query()
                        ->where('is_treasury', false)
                        ->when($get('farm_id'), fn($q) => $q->where('farm_id', $get('farm_id')))
                        ->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class)
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
