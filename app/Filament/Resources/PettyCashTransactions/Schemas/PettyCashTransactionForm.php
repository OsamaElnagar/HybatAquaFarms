<?php

namespace App\Filament\Resources\PettyCashTransactions\Schemas;

use App\Models\ExpenseCategory;
use App\Models\PettyCash;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PettyCashTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('العهدة والنوع')
                    ->schema([
                        Select::make('petty_cash_id')
                            ->label('العهدة')
                            ->relationship('pettyCash', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $pettyCash = PettyCash::find($state);
                                    $set('current_balance', number_format($pettyCash->current_balance, 0));
                                }
                            })
                            ->searchable()
                            ->preload(),
                        TextEntry::make('current_balance')
                            ->label('رصيد العهده الحالي')
                            ->placeholder('حدد عهده للبدأ'),
                        // ->visible(fn ($get) => filled($get('petty_cash_id'))),
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
                            ->relationship('expenseCategory', 'name', fn ($query) => $query->where('is_active', true))
                            ->visible(fn ($get) => $get('direction') === 'out')
                            ->required(fn ($get) => $get('direction') === 'out')
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function ($get) {
                                if ($get('direction') !== 'out') {
                                    return false;
                                }
                                $categoryId = $get('expense_category_id');
                                if (! $categoryId) {
                                    return false;
                                }
                                $category = ExpenseCategory::find($categoryId);

                                return $category && $category->code === 'WORKER_SALARY';
                            })
                            ->required(function ($get) {
                                if ($get('direction') !== 'out') {
                                    return false;
                                }
                                $categoryId = $get('expense_category_id');
                                if (! $categoryId) {
                                    return false;
                                }
                                $category = ExpenseCategory::find($categoryId);

                                return $category && $category->code === 'WORKER_SALARY';
                            }),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('المبلغ والوصف')
                    ->schema([
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->suffix(' EGP '),
                        Textarea::make('description')
                            ->label('الوصف التفصيلي')
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->helperText('اكتب تفاصيل المصروف/التزويد بالتفصيل'),
                        // Placeholder::make('balance_after')
                        //     ->label('الرصيد بعد المعاملة')
                        //     ->content(function ($get, $record) {
                        //         $amount = (float) ($get('amount') ?? $record?->amount ?? 0);
                        //         $direction = $get('direction') ?? $record?->direction ?? 'out';
                        //         $pettyCashId = $get('petty_cash_id') ?? $record?->petty_cash_id;

                        //         if ($pettyCashId) {
                        //             $pettyCash = PettyCash::find($pettyCashId);
                        //             $currentBalance = $pettyCash->current_balance;
                        //             $balanceAfter = $direction === 'in'
                        //                 ? $currentBalance + $amount
                        //                 : $currentBalance - $amount;

                        //             return number_format($balanceAfter).'  EGP ';
                        //         }

                        //         return '-';
                        //     })
                        //     ->visible(fn ($get) => filled($get('petty_cash_id')) && filled($get('amount'))),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
