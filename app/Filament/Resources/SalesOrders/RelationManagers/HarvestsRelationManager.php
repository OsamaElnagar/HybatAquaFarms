<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

use App\Enums\HarvestStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HarvestsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvests';

    protected static ?string $title = 'سجلات الحصاد';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('harvest_number')
            ->columns([
                TextColumn::make('harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harvest_date')
                    ->label('تاريخ الحصاد')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->sortable(),
                TextColumn::make('boxes_count')
                    ->label('الصناديق')
                    ->numeric()
                    ->state(fn ($record) => $record->total_boxes)
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المجموع')
                            ->using(fn ($query) => $query->get()->sum('total_boxes')),
                    ]),
                TextColumn::make('total_weight')
                    ->label('الوزن الإجمالي')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->state(fn ($record) => $record->total_weight)
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 3)
                            ->suffix(' كجم')
                            ->using(fn ($query) => $query->get()->sum('total_weight')),
                    ]),
                TextColumn::make('total_quantity')
                    ->label('العدد')
                    ->numeric()
                    ->state(fn ($record) => $record->total_quantity)
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المجموع')
                            ->using(fn ($query) => $query->get()->sum('total_quantity')),
                    ]),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(HarvestStatus::class)
                    ->native(false),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('harvest_date', 'desc');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('المعلومات الأساسية')
                ->description('بيانات جلسة الحصاد')
                ->schema([
                    Select::make('harvest_operation_id')
                        ->label('عملية الحصاد')
                        ->relationship('harvestOperation', 'operation_number')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('اختر عملية الحصاد المرتبطة'),

                    Select::make('batch_id')
                        ->label('الدفعة')
                        ->relationship('batch', 'batch_code')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('اختر الدفعة'),

                    Select::make('farm_id')
                        ->label('المزرعة')
                        ->relationship('farm', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('اختر المزرعة'),

                    TextInput::make('harvest_number')
                        ->label('رقم الحصاد')
                        ->default(fn () => \App\Models\Harvest::generateHarvestNumber())
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->disabled()
                        ->dehydrated()
                        ->helperText('يتم التوليد تلقائياً'),

                    Select::make('status')
                        ->label('الحالة')
                        ->options(HarvestStatus::class)
                        ->default(HarvestStatus::Pending)
                        ->required()
                        ->native(false),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('التاريخ والفترة')
                ->description('موعد جلسة الحصاد')
                ->schema([
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

                    Hidden::make('recorded_by')
                        ->default(auth()->id()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('ملاحظات')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('أي ملاحظات إضافية عن جلسة الحصاد'),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpanFull(),
        ]);
    }
}
