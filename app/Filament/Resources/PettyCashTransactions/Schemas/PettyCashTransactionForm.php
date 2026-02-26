<?php

namespace App\Filament\Resources\PettyCashTransactions\Schemas;

use App\Enums\PettyTransacionType;
use App\Models\ExpenseCategory;
use App\Models\PettyCash;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
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
                            ->options(fn () => \Illuminate\Support\Facades\Cache::remember('petty_cashes_list', now()->addDay(), fn () => PettyCash::pluck('name', 'id')->toArray()))
                            ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $pettyCash = PettyCash::with('farms')->find($state);
                                    if ($pettyCash) {
                                        $set('current_balance', number_format($pettyCash->current_balance, 0));

                                        if ($pettyCash->farms->count() === 1) {
                                            $set('farm_id', $pettyCash->farms->first()->id);
                                        } else {
                                            $set('farm_id', null);
                                        }
                                    }
                                } else {
                                    $set('farm_id', null);
                                }
                            })
                            ->searchable()
                            ->preload(),
                        TextEntry::make('current_balance')
                            ->label('رصيد العهده الحالي')
                            ->placeholder('حدد عهده للبدأ.'),
                        // ->visible(fn ($get) => filled($get('petty_cash_id'))),

                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->options(function (callable $get) {
                                $pettyCashId = $get('petty_cash_id');
                                if (! $pettyCashId) {
                                    return [];
                                }

                                return \Illuminate\Support\Facades\Cache::remember("petty_cash_{$pettyCashId}_farms", now()->addDay(), function () use ($pettyCashId) {
                                    return \App\Models\Farm::whereHas('pettyCashes', function ($q) use ($pettyCashId) {
                                        $q->where('petty_cashes.id', $pettyCashId);
                                    })->pluck('name', 'id')->toArray();
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(fn (callable $set) => $set('batch_id', null))
                            ->visible(fn (callable $get) => filled($get('petty_cash_id')))
                            ->helperText('المزرعة التي تخصها المعاملة.'),

                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: function ($query, callable $get) {
                                $farmId = $get('farm_id');
                                if ($farmId) {
                                    $query->where('farm_id', $farmId)->where('is_cycle_closed', false);
                                } else {
                                    $query->whereRaw('1 = 0');
                                }

                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => filled($get('farm_id')) && $get('direction') === PettyTransacionType::OUT)
                            ->helperText('اختر دفعة الزريعة المفتوحة (اختياري).'),

                        Select::make('direction')
                            ->label('النوع')
                            ->options(PettyTransacionType::class)
                            ->required()
                            ->live()
                            ->default(PettyTransacionType::OUT),

                        Select::make('expense_category_id')
                            ->label('نوع المصروف')
                            ->relationship('expenseCategory', 'name', fn ($query) => $query->where('is_active', true))
                            ->visible(fn ($get) => $get('direction') === PettyTransacionType::OUT)
                            ->required(fn ($get) => $get('direction') === PettyTransacionType::OUT)
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function ($get) {
                                if ($get('direction') !== PettyTransacionType::OUT) {
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
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
