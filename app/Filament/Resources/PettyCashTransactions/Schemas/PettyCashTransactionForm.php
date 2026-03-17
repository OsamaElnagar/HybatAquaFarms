<?php

namespace App\Filament\Resources\PettyCashTransactions\Schemas;

use App\Enums\BatchCycleType;
use App\Enums\PettyTransacionType;
use App\Models\Batch;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\PettyCash;
use Cache;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PettyCashTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        $livewire = null;

        try {
            $livewire = $schema->getLivewire();
        } catch (\TypeError) {
            // livewire is not yet set on the schema, common in Bulk Transactions Action
        }

        $ownerRecord = ($livewire instanceof RelationManager) ? $livewire->getOwnerRecord() : null;
        $isPettyCashManager = $ownerRecord instanceof PettyCash;
        $isFarmManager = $ownerRecord instanceof Farm;

        return $schema
            ->components([
                Section::make('العهدة والنوع')
                    ->schema([
                        Select::make('petty_cash_id')
                            ->label('العهدة')
                            ->options(function() use ($isFarmManager, $isPettyCashManager, $ownerRecord) {
                                if ($isFarmManager) {
                                    return $ownerRecord->pettyCashes()->pluck('name', 'petty_cashes.id')->toArray();
                                }
                                return Cache::remember('petty_cashes_list', now()->addDay(), fn() => PettyCash::pluck('name', 'id')->toArray());
                            })
                            ->default(function () use ($isPettyCashManager, $isFarmManager, $ownerRecord) {
                                if ($isPettyCashManager) {
                                    return $ownerRecord->getKey();
                                }

                                if ($isFarmManager && $ownerRecord->pettyCashes->count() === 1) {
                                    return $ownerRecord->pettyCashes->first()->id;
                                }

                                return null;
                            })
                            ->required()
                            ->live()
                            ->disabled($isPettyCashManager)
                            ->dehydrated()
                            ->afterStateHydrated(fn(Set $set, Get $get, $state) => self::updateDependentFields($set, $get, $state))
                            ->afterStateUpdated(fn(Set $set, Get $get, $state) => self::updateDependentFields($set, $get, $state))
                            ->searchable()
                            ->preload(),
                        TextEntry::make('current_balance')
                            ->label('رصيد العهده الحالي')
                            ->placeholder('حدد عهده للبدأ.'),

                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->options(function (Get $get) {
                                $pettyCashId = $get('petty_cash_id');
                                if (!$pettyCashId) {
                                    return [];
                                }

                                return Cache::remember("petty_cash_{$pettyCashId}_farms", now()->addMinutes(5), function () use ($pettyCashId) {
                                    return Farm::whereHas('pettyCashes', function ($q) use ($pettyCashId) {
                                        $q->where('petty_cashes.id', $pettyCashId);
                                    })->pluck('name', 'id')->toArray();
                                });
                            })
                            ->default(function () use ($isFarmManager, $isPettyCashManager, $ownerRecord) {
                                if ($isFarmManager) {
                                    return $ownerRecord->getKey();
                                }

                                if ($isPettyCashManager && $ownerRecord->farms->count() === 1) {
                                    return $ownerRecord->farms->first()->id;
                                }

                                return null;
                            })
                            ->disabled($isFarmManager)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->afterStateHydrated(fn($state, Set $set) => self::selectMainBatchForFarm($set, (int) $state))
                            ->afterStateUpdated(fn($state, Set $set) => self::selectMainBatchForFarm($set, (int) $state))
                            ->visible(fn(Get $get) => filled($get('petty_cash_id')) || $isFarmManager)
                            ->helperText('المزرعة التي تخصها المعاملة.'),

                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: function ($query, Get $get) {
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
                            ->visible(fn(Get $get) => filled($get('farm_id')) && $get('direction') === PettyTransacionType::OUT)
                            ->helperText('اختر دفعة الزريعة المفتوحة (اختياري).'),

                        Select::make('direction')
                            ->label('النوع')
                            ->options(PettyTransacionType::class)
                            ->required()
                            ->live()
                            ->default(PettyTransacionType::OUT),

                        Select::make('expense_category_id')
                            ->label('نوع المصروف')
                            ->relationship('expenseCategory', 'name', fn($query) => $query->where('is_active', true))
                            ->visible(fn($get) => $get('direction') === PettyTransacionType::OUT)
                            ->required(fn($get) => $get('direction') === PettyTransacionType::OUT)
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
                                if (!$categoryId) {
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
                            ->default(function () {
                                return Cache::get('user_' . auth('web')->id() . '_last_petty_cash_date') ?? now();
                            }),
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
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }

    private static function updateDependentFields(Set $set, Get $get, ?string $pettyCashId): void
    {
        if (!$pettyCashId) {
            $set('current_balance', null);

            return;
        }

        $pettyCash = PettyCash::with('farms')->find($pettyCashId);
        if ($pettyCash) {
            $set('current_balance', number_format($pettyCash->current_balance, 0));

            $availableFarmIds = $pettyCash->farms->pluck('id')->toArray();
            $currentFarmId = $get('farm_id');

            // If current farm is not in the list of farms for this petty cash, clear it
            if ($currentFarmId && !in_array($currentFarmId, $availableFarmIds)) {
                $set('farm_id', null);
                $set('batch_id', null);
                $currentFarmId = null;
            }

            // Auto-select farm if it's not set and there's only one farm
            if (!$currentFarmId && count($availableFarmIds) === 1) {
                $farmId = $availableFarmIds[0];
                $set('farm_id', $farmId);
                self::selectMainBatchForFarm($set, $farmId);
            }
        }
    }

    private static function selectMainBatchForFarm(Set $set, ?int $farmId): void
    {
        if (!$farmId) {
            $set('batch_id', null);

            return;
        }

        $mainActiveBatches = Batch::where('farm_id', $farmId)
            ->where('is_cycle_closed', false)
            ->where('cycle_type', BatchCycleType::Main)
            ->get();

        if ($mainActiveBatches->count() === 1) {
            $set('batch_id', $mainActiveBatches->first()->id);
        } else {
            $set('batch_id', null);
        }
    }
}
