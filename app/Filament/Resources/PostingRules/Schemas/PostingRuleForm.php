<?php

namespace App\Filament\Resources\PostingRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_key')
                    ->label('مفتاح الحدث')
                    ->required()
                    ->helperText('مفتاح فريد لتحديد نوع الحدث (مثل: voucher.payment, sales.cash)')
                    ->maxLength(100),
                TextInput::make('description')
                    ->label('الوصف')
                    ->required()
                    ->maxLength(500),
                Select::make('debit_account_id')
                    ->label('حساب المدين')
                    ->relationship('debitAccount', 'name')
                    ->searchable()
                    ->required(),
                Select::make('credit_account_id')
                    ->label('حساب الدائن')
                    ->relationship('creditAccount', 'name')
                    ->searchable()
                    ->required(),
                Textarea::make('options')
                    ->label('خيارات إضافية')
                    ->columnSpanFull()
                    ->helperText('خيارات إضافية بصيغة JSON (اختياري)'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
            ]);
    }
}
