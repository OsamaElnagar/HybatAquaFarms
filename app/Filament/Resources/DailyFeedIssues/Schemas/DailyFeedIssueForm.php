<?php

namespace App\Filament\Resources\DailyFeedIssues\Schemas;

use App\Models\FeedStock;
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
                            ->afterStateUpdated(fn(callable $set) => $set('unit_id', null))
                            ->helperText('يرجى اختيار المزرعة ذات الصلة'),
                        Select::make('unit_id')
                            ->label('الوحدة او الحوض')
                            ->required()
                            ->relationship('unit', modifyQueryUsing: fn($query, Get $get) => $query->where('farm_id', $get('farm_id')))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->code . ' - ' . $record->name)
                            ->searchable()
                            ->preload()
                            ->helperText('اختر وحدة تابعة للمزرعة المختارة (حوض/خزان)'),
                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->relationship('feedItem', 'name')
                            ->required()
                            ->helperText('اختر صنف العلف المصروف'),
                        Select::make('feed_warehouse_id')
                            ->label('مخزن العلف')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->helperText('حدد المخزن الذي تم صرف العلف منه'),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required()
                            ->maxDate(now()->tomorrow())
                            ->helperText('تاريخ عملية الصرف'),
                        TextInput::make('quantity')
                            ->label('الكمية (كجم)')
                            ->required()
                            ->numeric()
                            ->rule(fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
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
                            ->helperText('أدخل كمية العلف المصروف بالكيلو جرام'),
                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: function ($query, Get $get) {
                                $unitId = $get('unit_id');
                                if ($unitId) {
                                    return $query->whereHas('units', fn($q) => $q->where('farm_units.id', $unitId));
                                }

                                return $query->latest();
                            })
                            ->helperText('حدد دفعة الزريعة إذا كانت موجودة'),
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
                            ->columns(1)->columnSpanFull()->collapsed(),
                    ])
                    ->columns(2)->columnSpanFull(),


            ]);
    }
}
