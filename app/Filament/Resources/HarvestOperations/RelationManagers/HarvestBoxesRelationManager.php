<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HarvestBoxesRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestBoxes';

    protected static ?string $title = 'صناديق الحصاد';

    protected static ?string $recordTitleAttribute = 'box_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('harvest.harvest_number')
                    ->label('الحصاد')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('box_number')
                    ->label('رقم الصندوق')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('classification')
                    ->label('التصنيف')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn ($state) => match ($state) {
                        'جامبو' => 'success',
                        'بلطي' => 'info',
                        'نمرة 1' => 'warning',
                        'نمرة 2' => 'warning',
                        'نمرة 3' => 'gray',
                        'نمرة 4' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->numeric(decimalPlaces: 2)),

                TextColumn::make('fish_count')
                    ->label('العدد')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')),

                TextColumn::make('average_fish_weight')
                    ->label('متوسط الوزن (جم)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_sold')
                    ->label('مباع')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('subtotal')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP'))
                    ->placeholder('—')
                    ->weight('bold'),

                TextColumn::make('sold_at')
                    ->label('تاريخ البيع')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_sold')
                    ->label('حالة البيع')
                    ->placeholder('الكل')
                    ->trueLabel('مباع')
                    ->falseLabel('متاح')
                    ->native(false),

                SelectFilter::make('classification')
                    ->label('التصنيف')
                    ->options([
                        'بلطي' => 'بلطي',
                        'نمرة 1' => 'نمرة 1',
                        'نمرة 2' => 'نمرة 2',
                        'نمرة 3' => 'نمرة 3',
                        'نمرة 4' => 'نمرة 4',
                        'جامبو' => 'جامبو',
                        'خرط' => 'خرط',
                    ])
                    ->native(false),

                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة صندوق')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('box_number', 'asc');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('معلومات الصندوق الأساسية')
                ->description('البيانات الأساسية للصندوق')
                ->schema([
                    Hidden::make('harvest_operation_id')
                        ->default(fn ($livewire) => $livewire->ownerRecord->id),

                    Select::make('harvest_id')
                        ->label('الحصاد')
                        ->relationship(
                            'harvest',
                            'harvest_number',
                            fn ($query, $livewire) => $query->where('harvest_operation_id', $livewire->ownerRecord->id)
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state) {
                                $harvest = \App\Models\Harvest::find($state);
                                if ($harvest) {
                                    $set('batch_id', $harvest->batch_id);
                                    $set('species_id', $harvest->batch?->species_id);
                                }
                            }
                        })
                        ->createOptionForm([
                            TextInput::make('harvest_number')
                                ->label('رقم الحصاد')
                                ->default(fn () => \App\Models\Harvest::generateHarvestNumber())
                                ->required()
                                ->unique(),
                            \Filament\Forms\Components\DatePicker::make('harvest_date')
                                ->label('التاريخ')
                                ->default(now())
                                ->required(),
                            Select::make('shift')
                                ->label('الفترة')
                                ->options([
                                    'morning' => 'صباحي',
                                    'afternoon' => 'ظهري',
                                    'night' => 'مسائي',
                                ])
                                ->required(),
                        ]),

                    TextInput::make('box_number')
                        ->label('رقم الصندوق')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(function ($livewire) {
                            $lastBox = \App\Models\HarvestBox::where('harvest_operation_id', $livewire->ownerRecord->id)
                                ->max('box_number');

                            return ($lastBox ?? 0) + 1;
                        }),

                    Hidden::make('batch_id'),
                    Hidden::make('species_id'),
                ])
                ->columns(2)->columnSpanFull(),

            Section::make('التصنيف والدرجة')
                ->description('تصنيف ودرجة الأسماك')
                ->schema([
                    Select::make('classification')
                        ->label('التصنيف')
                        ->options([
                            'بلطي' => 'بلطي',
                            'نمرة 1' => 'نمرة 1',
                            'نمرة 2' => 'نمرة 2',
                            'نمرة 3' => 'نمرة 3',
                            'نمرة 4' => 'نمرة 4',
                            'جامبو' => 'جامبو',
                            'خرط' => 'خرط',
                        ])
                        ->searchable()
                        ->required(),

                    TextInput::make('grade')
                        ->label('الدرجة')
                        ->maxLength(50),

                    TextInput::make('size_category')
                        ->label('فئة الحجم')
                        ->maxLength(50),
                ])
                ->columns(3)->columnSpanFull(),

            Section::make('القياسات')
                ->description('الوزن والعدد')
                ->schema([
                    TextInput::make('weight')
                        ->label('الوزن (كجم)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(0.001)
                        ->suffix('كجم')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $fishCount = $get('fish_count');
                            if ($state && $fishCount) {
                                $avgWeight = ($state * 1000) / $fishCount;
                                $set('average_fish_weight', round($avgWeight, 3));
                            }
                        }),

                    TextInput::make('fish_count')
                        ->label('عدد الأسماك')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->suffix('سمكة')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $weight = $get('weight');
                            if ($state && $weight) {
                                $avgWeight = ($weight * 1000) / $state;
                                $set('average_fish_weight', round($avgWeight, 3));
                            }
                        }),

                    TextInput::make('average_fish_weight')
                        ->label('متوسط وزن السمكة (جم)')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->suffix('جم')
                        ->helperText('يتم الحساب تلقائياً'),
                ])
                ->columns(3)
                ->columnSpanFull(),

            Section::make('معلومات البيع')
                ->description('بيانات البيع والتسعير')
                ->schema([
                    Toggle::make('is_sold')
                        ->label('مباع')
                        ->live()
                        ->default(false),

                    Select::make('trader_id')
                        ->label('التاجر')
                        ->relationship('trader', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('is_sold'))
                        ->required(fn (Get $get) => $get('is_sold')),

                    TextInput::make('unit_price')
                        ->label('سعر الوحدة')
                        ->numeric()
                        ->prefix('EGP')
                        ->step(0.01)
                        ->visible(fn (Get $get) => $get('is_sold'))
                        ->required(fn (Get $get) => $get('is_sold'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $pricingUnit = $get('pricing_unit');
                            $weight = $get('weight');
                            $fishCount = $get('fish_count');

                            if ($state && $pricingUnit) {
                                $subtotal = match ($pricingUnit) {
                                    'kilogram' => $weight * $state,
                                    'piece' => $fishCount * $state,
                                    'box' => $state,
                                    default => $weight * $state,
                                };
                                $set('subtotal', round($subtotal, 2));
                            }
                        }),

                    Select::make('pricing_unit')
                        ->label('وحدة التسعير')
                        ->options([
                            'kilogram' => 'كيلوجرام',
                            'piece' => 'قطعة',
                            'box' => 'صندوق',
                        ])
                        ->default('kilogram')
                        ->visible(fn (Get $get) => $get('is_sold'))
                        ->required(fn (Get $get) => $get('is_sold'))
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $unitPrice = $get('unit_price');
                            $weight = $get('weight');
                            $fishCount = $get('fish_count');

                            if ($state && $unitPrice) {
                                $subtotal = match ($state) {
                                    'kilogram' => $weight * $unitPrice,
                                    'piece' => $fishCount * $unitPrice,
                                    'box' => $unitPrice,
                                    default => $weight * $unitPrice,
                                };
                                $set('subtotal', round($subtotal, 2));
                            }
                        }),

                    TextInput::make('subtotal')
                        ->label('الإجمالي')
                        ->numeric()
                        ->prefix('EGP')
                        ->disabled()
                        ->dehydrated()
                        ->visible(fn (Get $get) => $get('is_sold'))
                        ->helperText('يتم الحساب تلقائياً'),

                    DateTimePicker::make('sold_at')
                        ->label('تاريخ البيع')
                        ->visible(fn (Get $get) => $get('is_sold'))
                        ->default(now())
                        ->seconds(false),
                ])
                ->columns(3)->columnSpanFull(),

            Section::make('ملاحظات')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpanFull(),
        ]);
    }
}
