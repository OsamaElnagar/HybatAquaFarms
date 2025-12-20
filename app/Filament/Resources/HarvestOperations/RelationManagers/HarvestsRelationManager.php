<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Enums\HarvestStatus;
use Filament\Actions\CreateAction;
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

    protected static ?string $title = 'جلسات الحصاد (Daily Sessions)';

    protected static ?string $recordTitleAttribute = 'harvest_number';

    public function table(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'morning' => 'صباحي',
                        'afternoon' => 'ظهري',
                        'night' => 'مسائي',
                        default => '—'
                    })
                    ->color(fn ($state) => match ($state) {
                        'morning' => 'warning',
                        'afternoon' => 'info',
                        'night' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('total_boxes')
                    ->label('الصناديق')
                    ->numeric(),

                TextColumn::make('total_weight')
                    ->label('الوزن (كجم)')
                    ->numeric(),

                TextColumn::make('total_quantity')
                    ->label('عدد الأسماك')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('soldBoxesCount')
                    ->label('مباع')
                    ->numeric()
                    ->color('success'),

                TextColumn::make('recordedBy.name')
                    ->label('المسجل')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('تسجيل حصاد جديد')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('harvest_date', 'desc');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('المعلومات الأساسية')
                ->description('بيانات جلسة الحصاد')
                ->schema([
                    Hidden::make('harvest_operation_id')
                        ->default(fn ($livewire) => $livewire->ownerRecord->id),

                    Hidden::make('batch_id')
                        ->default(fn ($livewire) => $livewire->ownerRecord->batch_id),

                    Hidden::make('farm_id')
                        ->default(fn ($livewire) => $livewire->ownerRecord->farm_id),

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
                ->columns(2)->columnSpanFull(),

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
        ]);
    }
}
