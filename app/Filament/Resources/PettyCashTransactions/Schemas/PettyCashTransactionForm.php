<?php

namespace App\Filament\Resources\PettyCashTransactions\Schemas;

use App\Models\PettyCash;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PettyCashTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('petty_cash_id')
                    ->label('العهدة')
                    ->relationship('pettyCash', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $pettyCash = PettyCash::find($state);
                            $set('current_balance', number_format($pettyCash->current_balance, 2));
                        }
                    })
                    ->searchable()
                    ->preload(),
                Placeholder::make('current_balance')
                    ->label('الرصيد الحالي')
                    ->content(function ($get) {
                        $pettyCashId = $get('petty_cash_id');
                        if ($pettyCashId) {
                            $pettyCash = PettyCash::find($pettyCashId);

                            return number_format($pettyCash->current_balance ?? 0, 2).' جنيه';
                        }

                        return '0.00 جنيه';
                    })
                    ->visible(fn ($get) => filled($get('petty_cash_id'))),
                Select::make('direction')
                    ->label('النوع')
                    ->options([
                        'out' => 'صرف (مصروف)',
                        'in' => 'قبض (تزويد)',
                    ])
                    ->required()
                    ->live()
                    ->default('out'),
                Select::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name_arabic', fn ($query) => $query->where('is_active', true))
                    ->visible(fn ($get) => $get('direction') === 'out')
                    ->required(fn ($get) => $get('direction') === 'out')
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0.01)
                    ->step(0.01),
                Textarea::make('description')
                    ->label('الوصف التفصيلي')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(1000)
                    ->helperText('اكتب تفاصيل المصروف/التزويد بالتفصيل'),
                Placeholder::make('balance_after')
                    ->label('الرصيد بعد المعاملة')
                    ->content(function ($get, $record) {
                        $amount = (float) ($get('amount') ?? $record?->amount ?? 0);
                        $direction = $get('direction') ?? $record?->direction ?? 'out';
                        $pettyCashId = $get('petty_cash_id') ?? $record?->petty_cash_id;

                        if ($pettyCashId) {
                            $pettyCash = PettyCash::find($pettyCashId);
                            $currentBalance = $pettyCash->current_balance;
                            $balanceAfter = $direction === 'in'
                                ? $currentBalance + $amount
                                : $currentBalance - $amount;

                            return number_format($balanceAfter, 2).' جنيه';
                        }

                        return '-';
                    })
                    ->visible(fn ($get) => filled($get('petty_cash_id')) && filled($get('amount'))),
            ]);
    }
}
