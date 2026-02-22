<?php

namespace App\Filament\Resources\PettyCashTransactions\Schemas;

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
                            ->relationship('pettyCash', 'name')
                            ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_id'))
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
                            ->placeholder('حدد عهده للبدأ. (يتم تحديد آخر عهدة تم استخدامها تلقائياً)'),
                        // ->visible(fn ($get) => filled($get('petty_cash_id'))),

                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name', modifyQueryUsing: function ($query, callable $get) {
                                $pettyCashId = $get('petty_cash_id');
                                if ($pettyCashId) {
                                    $query->whereHas('pettyCashes', function ($q) use ($pettyCashId) {
                                        $q->where('petty_cashes.id', $pettyCashId);
                                    });
                                } else {
                                    $query->whereRaw('1 = 0');
                                }

                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(fn (callable $set) => $set('batch_id', null))
                            ->visible(fn (callable $get) => filled($get('petty_cash_id')))
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_farm_id'))
                            ->helperText('المزرعة التي تخصها المعاملة. (يتم تحديد آخر مزرعة تم استخدامها تلقائياً)'),

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
                            ->visible(fn (callable $get) => filled($get('farm_id')) && $get('direction') === 'out')
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_batch_id'))
                            ->helperText('اختر دفعة الزريعة المفتوحة (اختياري). (يتم تحديد آخر دفعة تم استخدامها تلقائياً)'),

                        Select::make('direction')
                            ->label('النوع')
                            ->options([
                                'out' => 'صرف',
                                'in' => 'قبض',
                            ])
                            ->required()
                            ->live()
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_direction') ?? 'out')
                            ->helperText('(يتم تحديد آخر نوع تم استخدامه تلقائياً)'),
                        Select::make('expense_category_id')
                            ->label('نوع المصروف')
                            ->relationship('expenseCategory', 'name', fn ($query) => $query->where('is_active', true))
                            ->visible(fn ($get) => $get('direction') === 'out')
                            ->required(fn ($get) => $get('direction') === 'out')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_category_id'))
                            ->helperText('(يتم تحديد آخر نوع مصروف تم استخدامه تلقائياً)'),
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
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_employee_id'))
                            ->helperText('(يتم تحديد آخر موظف تم استخدامه تلقائياً)'),
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
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_amount'))
                            ->suffix(' EGP ')
                            ->helperText('(يتم إدخال آخر مبلغ تم استخدامه تلقائياً)'),
                        Textarea::make('description')
                            ->label('الوصف التفصيلي')
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->default(fn () => \Illuminate\Support\Facades\Cache::get('user_'.auth('web')->id().'_last_petty_cash_description'))
                            ->helperText('اكتب تفاصيل المصروف/التزويد بالتفصيل. (يتم إدخال آخر وصف تم استخدامه تلقائياً)'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
