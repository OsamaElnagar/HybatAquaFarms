<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Enums\HarvestStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HarvestsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvests';

    protected static ?string $title = '(الصياده) جلسات الحصاد';

    protected static ?string $recordTitleAttribute = 'harvest_number';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with('orders.items'))
            ->columns([
                TextColumn::make('harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('harvest_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('shift')
                    ->label('الفترة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'morning' => 'صباحي',
                        'afternoon' => 'ظهري',
                        'night' => 'مسائي',
                        default => '—'
                    })
                    ->color(fn($state) => match ($state) {
                        'morning' => 'warning',
                        'afternoon' => 'info',
                        'night' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('عدد الإيصالات')
                    ->numeric(locale: 'en')
                    ->sortable()
                    ->summarize(
                        Summarizer::make()
                            ->label('الإجمالي')
                            ->numeric(locale: 'en')
                            ->using(
                                fn(\Illuminate\Database\Query\Builder $query): int => \App\Models\Order::whereIn('harvest_id', (clone $query)->pluck('harvests.id'))->count()
                            )
                    ),

                TextColumn::make('total_boxes')
                    ->label('إجمالي البكس')
                    ->state(function ($record) {
                        return $record->orders->sum(function ($order) {
                            return $order->items->sum('quantity');
                        });
                    })
                    ->numeric(locale: 'en')
                    ->summarize(
                        Summarizer::make()
                            ->label('الإجمالي')
                            ->numeric(locale: 'en')
                            ->using(
                                fn(\Illuminate\Database\Query\Builder $query): int => (int) \App\Models\OrderItem::whereHas(
                                    'order',
                                    fn($q) => $q->whereIn('harvest_id', (clone $query)->pluck('harvests.id'))
                                )->sum('quantity')
                            )
                    ),

                TextColumn::make('total_weight')
                    ->label('إجمالي الوزن')
                    ->state(function ($record) {
                        return $record->orders->sum(function ($order) {
                            return $order->items->sum('total_weight');
                        });
                    })
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->summarize(
                        Summarizer::make()
                            ->label('الإجمالي')
                            ->numeric(decimalPlaces: 0, locale: 'en')
                            ->using(
                                fn(\Illuminate\Database\Query\Builder $query): float => (float) \App\Models\OrderItem::whereHas(
                                    'order',
                                    fn($q) => $q->whereIn('harvest_id', (clone $query)->pluck('harvests.id'))
                                )->sum('total_weight')
                            )
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(HarvestStatus::class)
                    ->native(false),

                SelectFilter::make('shift')
                    ->label('الفترة')
                    ->options([
                        'morning' => 'صباحي',
                        'afternoon' => 'ظهري',
                        'night' => 'مسائي',
                    ])
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('جلسة حصاد جديده')
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])->toolbarActions([
                    DeleteBulkAction::make(),
                ])

            ->defaultSort('harvest_date', 'desc');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('المعلومات الأساسية')
                ->description('بيانات جلسة الحصاد')
                ->schema([
                    TextInput::make('harvest_number')
                        ->label('رقم الحصاد')
                        ->default(fn() => \App\Models\Harvest::generateHarvestNumber())
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->disabled()
                        ->dehydrated()
                        ->helperText('يتم التوليد تلقائياً'),
                    DatePicker::make('harvest_date')
                        ->label('تاريخ الحصاد')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->maxDate(now()),

                    Select::make('shift')
                        ->label('الفترة')
                        ->options([
                            'morning' => 'صباحي',
                            'afternoon' => 'ظهري',
                            'night' => 'مسائي',
                        ])
                        ->required()
                        ->native(false)
                        ->default('morning'),
                    Select::make('status')
                        ->label('الحالة')
                        ->options(HarvestStatus::class)
                        ->default(HarvestStatus::Pending)
                        ->required()
                        ->native(false),
                ])
                ->columns(2)->columnSpanFull(),

            Section::make('ملاحظات')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(4)
                        ->maxLength(1000)

                        ->placeholder('أي ملاحظات إضافية عن جلسة الحصاد'),
                ])->collapsible()
                ->collapsed()
                ->columnSpanFull(),

            Section::make('الإيصالات والعناصر')
                ->schema([
                    Repeater::make('orders')
                        ->relationship('orders')
                        ->label('الإيصالات (الأوردرات)')
                        ->schema([
                            TextInput::make('code')
                                ->label('الكود')
                                ->disabled()
                                ->dehydrated()
                                ->helperText('يتم إنشاؤه تلقائياً'),
                            Select::make('trader_id')
                                ->label('التاجر')
                                ->relationship('trader', 'name')
                                ->required(),
                            Select::make('driver_id')
                                ->label('السائق')
                                ->relationship('driver', 'name'),
                            DatePicker::make('date')
                                ->label('التاريخ')
                                ->displayFormat('Y-m-d')
                                ->native(false)
                                ->required()
                                ->default(now()),
                            Textarea::make('notes')
                                ->label('ملاحظات'),

                            Repeater::make('items')
                                ->relationship('items')
                                ->label('عناصر الإيصال (البكس)')
                                ->schema([
                                    Select::make('box_id')
                                        ->label('صنف البوكسه')
                                        ->relationship('box', 'name', modifyQueryUsing: function (Builder $query) {
                                            return $query->select('*');
                                        })
                                        ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->full_name)
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    // ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                    TextInput::make('quantity')
                                        ->label('عدد البكس')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1),
                                    TextInput::make('weight_per_box')
                                        ->label('وزن البوكسه')
                                        ->default(25)
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->addActionLabel('إضافة بوكس جديد')
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->collapsed(false)
                        ->itemLabel(fn(array $state): ?string => $state['code'] ?? null)
                        ->columns(2)
                        ->addActionLabel('إضافة إيصال جديد'),
                ])->columnSpanFull(),
        ]);
    }
}
