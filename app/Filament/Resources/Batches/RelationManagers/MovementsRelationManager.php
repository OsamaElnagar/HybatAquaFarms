<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\MovementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'حركات الدفعة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(MovementType::class)
                    ->required()
                    ->native(false)
                    ->live(),
                Select::make('from_farm_id')
                    ->label('من المزرعة')
                    ->relationship('fromFarm', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => in_array($get('movement_type'), ['transfer', 'harvest', 'mortality'])),
                Select::make('to_farm_id')
                    ->label('إلى المزرعة')
                    ->relationship('toFarm', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => in_array($get('movement_type'), ['entry', 'transfer'])),
                Select::make('from_unit_id')
                    ->label('من الوحدة')
                    ->relationship('fromUnit', 'code')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => in_array($get('movement_type'), ['transfer', 'harvest', 'mortality'])),
                Select::make('to_unit_id')
                    ->label('إلى الوحدة')
                    ->relationship('toUnit', 'code')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => in_array($get('movement_type'), ['entry', 'transfer'])),
                TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $movementType = $get('movement_type');
                                $ownerRecord = $this->getOwnerRecord();

                                if (! $movementType || ! $ownerRecord) {
                                    return;
                                }

                                // For movements that decrease quantity
                                if (in_array($movementType, ['transfer', 'harvest', 'mortality'])) {
                                    if ($value > $ownerRecord->current_quantity) {
                                        $fail("الكمية ({$value}) تتجاوز الكمية المتاحة في الدفعة ({$ownerRecord->current_quantity}).");
                                    }
                                }
                            };
                        },
                    ])
                    ->helperText(function () {
                        $ownerRecord = $this->getOwnerRecord();
                        if (! $ownerRecord) {
                            return 'عدد الأسماك/الزريعة';
                        }

                        return "عدد الأسماك/الزريعة (المتاح: {$ownerRecord->current_quantity})";
                    }),
                TextInput::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric()
                    ->step(0.001)
                    ->suffix(' كجم'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now())
                    ->displayFormat('Y-m-d')
                    ->native(false),
                TextInput::make('reason')
                    ->label('السبب')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('movement_type')
                    ->label('نوع الحركة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('fromFarm.name')
                    ->label('من المزرعة')
                    ->toggleable(),
                TextColumn::make('toFarm.name')
                    ->label('إلى المزرعة')
                    ->toggleable(),
                TextColumn::make('fromUnit.code')
                    ->label('من الوحدة')
                    ->toggleable(),
                TextColumn::make('toUnit.code')
                    ->label('إلى الوحدة')
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('السبب')
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(MovementType::class)
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة حركة زريعه')
                    ->mutateDataUsing(function (array $data): array {
                        $data['batch_id'] = $this->getOwnerRecord()->id;
                        $data['recorded_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
