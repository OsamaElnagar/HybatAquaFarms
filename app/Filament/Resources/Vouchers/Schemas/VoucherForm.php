<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Enums\CounterpartyType;
use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                Select::make('batch_id')
                    ->label('دفعة الزريعه')
                    ->relationship('batch', 'batch_code')
                    ->required(),
                Select::make('voucher_type')
                    ->label('نوع السند')
                    ->options(VoucherType::class)
                    ->required(),
                TextInput::make('voucher_number')
                    ->label('رقم السند')
                    ->disabled()
                    ->dehydrated()
                    ->hint('يتم توليده تلقائياً'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                Radio::make('counterparty_type')
                    ->label('نوع الطرف الآخر')
                    ->options(CounterpartyType::class)
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('counterparty_id', null))
                    ->required(),
                Select::make('counterparty_id')
                    ->label(
                        fn (Get $get) => $get('counterparty_type')
                            ? $get('counterparty_type')->getLabel()
                            : 'اختر نوع الطرف الآخر أولاً'
                    )
                    ->options(function (Get $get) {
                        $type = $get('counterparty_type');

                        if (! $type) {
                            return [];
                        }

                        // $type is already a CounterpartyType enum instance, so just get its value
                        $modelClass = $type->value;

                        return $modelClass::pluck('name', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Get $get) => filled($get('counterparty_type'))),

                Select::make('treasury_account_id')
                    ->label('الخزنة / البنك')
                    ->relationship('treasuryAccount', 'name', fn ($query, $get) => $query
                        ->where('is_treasury', true)
                        ->when($get('farm_id'), fn ($q) => $q->where('farm_id', $get('farm_id'))))
                    ->required()
                    ->preload()
                    ->searchable(),
                Select::make('account_id')
                    ->label('حساب البند (مصروف/إيراد/عميل)')
                    ->relationship('account', 'name', fn ($query, $get) => $query
                        ->where('is_treasury', false)
                        ->when($get('farm_id'), fn ($q) => $q->where('farm_id', $get('farm_id'))))
                    ->required()
                    ->preload()
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
