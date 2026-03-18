<?php

namespace App\Filament\Resources\DailyFeedIssues\Schemas;

use App\Enums\BatchCycleType;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\FeedItem;
use App\Models\FeedStock;
use App\Models\FeedWarehouse;
use Cache;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DailyFeedIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        $livewire = null;
        try {
            $livewire = $schema->getLivewire();
        } catch (\TypeError) {
            // livewire is not yet set on the schema
        }

        $ownerRecord = ($livewire instanceof RelationManager) ? $livewire->getOwnerRecord() : null;
        $isFarm = $ownerRecord instanceof Farm;
        $isBatch = $ownerRecord instanceof Batch;
        $isFarmUnit = $ownerRecord instanceof FarmUnit;

        return $schema
            ->components([
                Section::make('معلومات الموقع وتفاصيل الصرف')
                    ->description('يرجى اختيار المزرعة والوحدة وملء بيانات صرف العلف')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->options(fn () => Cache::remember('active_farms_list', now()->addDay(), fn () => Farm::query()->active()->pluck('name', 'id')->toArray()))
                            ->default(function () use ($isFarm, $isBatch, $isFarmUnit, $ownerRecord) {
                                if ($isFarm) {
                                    return $ownerRecord->getKey();
                                }
                                if ($isBatch) {
                                    return $ownerRecord->farm_id;
                                }
                                if ($isFarmUnit) {
                                    return $ownerRecord->farm_id;
                                }

                                return Cache::get('user_'.auth('web')->id().'_last_farm_id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled($isFarm || $isBatch || $isFarmUnit)
                            ->dehydrated()
                            ->afterStateHydrated(fn (Set $set, Get $get, $state) => self::updateDependentFields($set, $get, (int) $state))
                            ->afterStateUpdated(fn (Set $set, Get $get, $state) => self::updateDependentFields($set, $get, (int) $state))
                            ->helperText('يرجى اختيار المزرعة ذات الصلة.'),

                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: function ($query, Get $get) {
                                $farmId = $get('farm_id');
                                if ($farmId) {
                                    return $query->where('farm_id', $farmId)->where('is_cycle_closed', false);
                                }

                                return $query->where('is_cycle_closed', false)->latest();
                            })
                            ->default(fn () => $isBatch ? $ownerRecord->getKey() : null)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled($isBatch)
                            ->dehydrated()
                            ->live()
                            ->afterStateHydrated(fn ($state, Set $set) => self::onBatchSelected($set, (int) $state))
                            ->afterStateUpdated(fn ($state, Set $set) => self::onBatchSelected($set, (int) $state))
                            ->helperText('دفعة الزريعة التي يتم صرف العلف لها.'),

                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->options(fn () => Cache::remember('active_feed_items_list', now()->addDay(), fn () => FeedItem::where('is_active', true)->pluck('name', 'id')->toArray()))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    Cache::put('user_'.auth('web')->id().'_last_feed_item_id', $state, now()->addDay());
                                }
                            })
                            ->helperText('اختر صنف العلف المصروف.'),

                        Select::make('feed_warehouse_id')
                            ->label('مخزن العلف')
                            ->relationship('warehouse', 'name', modifyQueryUsing: function ($query, Get $get) {
                                $farmId = $get('farm_id');
                                if ($farmId) {
                                    return $query->where('farm_id', $farmId)->where('is_active', true);
                                }

                                return $query->where('is_active', true);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    Cache::put('user_'.auth('web')->id().'_last_feed_warehouse_id', $state, now()->addDay());
                                }
                            })
                            ->helperText('حدد المخزن الذي تم صرف العلف منه.'),

                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required()
                            ->default(fn () => Cache::get('user_'.auth('web')->id().'_last_feed_issue_date') ?? now())
                            ->maxDate(now()->tomorrow())
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    Cache::put('user_'.auth('web')->id().'_last_feed_issue_date', $state, now()->addDay());
                                }
                            })
                            ->helperText('تاريخ عملية الصرف'),

                        TextInput::make('quantity')
                            ->label('الكمية (كجم)')
                            ->required()
                            ->numeric()
                            ->suffix(' كجم ')
                            ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $warehouseId = $get('feed_warehouse_id');
                                $itemId = $get('feed_item_id');

                                if (! $warehouseId || ! $itemId || $value === null || $value === '') {
                                    return;
                                }

                                $quantity = (float) $value;

                                $stock = FeedStock::where('feed_warehouse_id', $warehouseId)
                                    ->where('feed_item_id', $itemId)
                                    ->first();

                                if (! $stock || (float) $stock->quantity_in_stock < $quantity) {
                                    $fail('الكمية المصروفة أكبر من الرصيد المتوفر في المخزن لهذا الصنف.');
                                }
                            })
                            ->helperText('أدخل كمية العلف المصروف بالكيلو جرام.'),
                        Hidden::make('recorded_by')
                            ->default(fn () => auth('web')->id()),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->helperText('أضف أية ملاحظات إضافية متعلقة بالصرف'),
                    ])
                    ->columns(3)->columnSpanFull(),
            ]);
    }

    private static function updateDependentFields(Set $set, Get $get, ?int $farmId): void
    {
        if (! $farmId) {
            return;
        }

        // Auto-select first active warehouse if none selected or if selected belongs to another farm
        $currentWarehouseId = $get('feed_warehouse_id');
        $validWarehouse = false;
        if ($currentWarehouseId) {
            $validWarehouse = FeedWarehouse::where('id', $currentWarehouseId)->where('farm_id', $farmId)->exists();
        }

        if (! $validWarehouse) {
            $warehouse = FeedWarehouse::where('farm_id', $farmId)->where('is_active', true)->first();
            if ($warehouse) {
                $set('feed_warehouse_id', $warehouse->id);
            } else {
                $set('feed_warehouse_id', null);
            }
        }

        // Auto-select batch if not set
        if (! $get('batch_id')) {
            self::selectBatchForFarm($set, $farmId);
        }
    }

    private static function onBatchSelected(Set $set, ?int $batchId): void
    {
        if (! $batchId) {
            return;
        }

        $batch = Batch::find($batchId);
        if ($batch) {
            $set('farm_id', $batch->farm_id);
        }
    }

    private static function selectBatchForFarm(Set $set, ?int $farmId): void
    {
        if (! $farmId) {
            return;
        }

        $activeBatches = Batch::query()
            ->where('farm_id', $farmId)
            ->where('is_cycle_closed', false)
            ->where('cycle_type', BatchCycleType::Main)
            ->get();

        if ($activeBatches->count() === 1) {
            $set('batch_id', $activeBatches->first()->id);
        }
    }
}
