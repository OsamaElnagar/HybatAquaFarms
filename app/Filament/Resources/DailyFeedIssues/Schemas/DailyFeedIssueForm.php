<?php

namespace App\Filament\Resources\DailyFeedIssues\Schemas;

use App\Models\FeedStock;
use Cache;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DailyFeedIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الموقع وتفاصيل الصرف')
                    ->description('يرجى اختيار المزرعة والوحدة وملء بيانات صرف العلف')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->default(fn($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                            ->relationship('farm', 'name', modifyQueryUsing: fn($query) => $query->active()->latest())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('batch_id', null);
                                if ($state) {
                                    $warehouse = \App\Models\FeedWarehouse::where('farm_id', $state)->where('is_active', true)->first();
                                    if ($warehouse) {
                                        $set('feed_warehouse_id', $warehouse->id);
                                    }
                                }
                            })
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
                            ->default(fn($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)

                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->relationship('feedItem', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر صنف العلف المصروف.'),

                        Select::make('feed_warehouse_id')
                            ->label('مخزن العلف')
                            ->relationship('warehouse', 'name', modifyQueryUsing: fn($query, Get $get) => $query->where('farm_id', $get('farm_id')))
                            ->default(function ($livewire, Get $get) {
                                $farmId = $get('farm_id') ?: ($livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null);
                                if ($farmId) {
                                    return \App\Models\FeedWarehouse::where('farm_id', $farmId)->where('is_active', true)->first()?->id;
                                }

                                return Cache::get('user_' . auth('web')->id() . '_last_warehouse_id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('حدد المخزن الذي تم صرف العلف منه.'),

                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required()
                            ->default(now())
                            ->maxDate(now()->tomorrow())
                            ->helperText('تاريخ عملية الصرف'),

                        TextInput::make('quantity')
                            ->label('الكمية (كجم)')
                            ->required()
                            ->numeric()
                            ->rule(fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $warehouseId = $get('feed_warehouse_id');
                                $itemId = $get('feed_item_id');

                                if (!$warehouseId || !$itemId || $value === null || $value === '') {
                                    return;
                                }

                                $quantity = (float) $value;

                                $stock = FeedStock::where('feed_warehouse_id', $warehouseId)
                                    ->where('feed_item_id', $itemId)
                                    ->first();

                                if (!$stock || (float) $stock->quantity_in_stock < $quantity) {
                                    $fail('الكمية المصروفة أكبر من الرصيد المتوفر في المخزن لهذا الصنف.');
                                }
                            })
                            ->helperText('أدخل كمية العلف المصروف بالكيلو جرام.'),

                        Section::make('إضافات وملاحظات')
                            ->description('معلومات المستخدم والملاحظات الإضافية')
                            ->schema([
                                Select::make('recorded_by')
                                    ->label('سجل بواسطة')
                                    ->relationship('recordedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(fn() => auth('web')->id())
                                    ->helperText('المستخدم الذي قام بتسجيل عملية الصرف'),
                                Textarea::make('notes')
                                    ->label('ملاحظات')
                                    ->columnSpanFull()
                                    ->maxLength(1000)
                                    ->helperText('أضف أية ملاحظات إضافية متعلقة بالصرف'),
                            ])
                            ->columns(1)->columnSpanFull(),
                    ])
                    ->columns(2)->columnSpanFull(),

            ]);
    }
}
