<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Enums\CounterpartyType;
use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use App\Models\Account;
use App\Models\Batch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state) {
                        if ($state) {
                            $batch = Batch::find($state);
                            if ($batch) {
                                $set('farm_id', $batch->farm_id);
                            }
                        }
                    }),
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
                    ->required()
                    ->displayFormat('Y-m-d')
                    ->native(false),
                Radio::make('counterparty_type')
                    ->label('نوع الطرف الآخر')
                    ->options(CounterpartyType::class)
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('counterparty_id', null))
                    ->required(),
                Select::make('counterparty_id')
                    ->label(
                        fn(Get $get) => $get('counterparty_type')
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
                    ->visible(fn(Get $get) => filled($get('counterparty_type'))),

                Select::make('treasury_account_id')
                    ->label('الخزنة / البنك')
                    ->relationship('treasuryAccount', 'name', fn($query, Get $get) => $query
                        ->where('is_treasury', true)
                        ->when($get('farm_id'), fn($q) => $q->where(function ($q2) use ($get) {
                            $q2->where('farm_id', $get('farm_id'))
                                ->orWhereNull('farm_id');
                        })))
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state) {
                        if ($state) {
                            $account = Account::find($state);
                            if ($account) {
                                $set('farm_id', $account->farm_id);
                            }
                        }
                    })
                    ->required()
                    ->preload()
                    ->searchable(),
                Select::make('account_id')
                    ->label('حساب البند (مصروف/إيراد/عميل)')
                    ->options(function (Get $get) {
                        $query = Account::query()
                            // ->where('is_treasury', false)
                            ->where('is_active', true);
                        $farmId = $get('farm_id');
                        if ($farmId) {
                            $query->where(function ($q) use ($farmId) {
                                $q->where('farm_id', $farmId)
                                    ->orWhereNull('farm_id');
                            });
                        }

                        return $query->orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->live()
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
                Hidden::make('created_by')
                    ->default(auth('web')->id()),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
