<?php

namespace App\Filament\Resources\BatchMovements\Tables;

use App\Enums\MovementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BatchMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.batch_code')
                    ->label('كود الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label('نوع الحركة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\MovementType ? $state->label() : $state)
                    ->color(fn ($state) => match ($state instanceof \App\Enums\MovementType ? $state->value : $state) {
                        'entry' => 'success',
                        'transfer' => 'info',
                        'harvest' => 'warning',
                        'mortality' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromFarm.name')
                    ->label('من المزرعة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('toFarm.name')
                    ->label('إلى المزرعة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('fromUnit.code')
                    ->label('من الوحدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('toUnit.code')
                    ->label('إلى الوحدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('السبب')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(MovementType::class)
                    ->native(false),
                SelectFilter::make('batch_id')
                    ->label('الدفعة')
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('from_farm_id')
                    ->label('من المزرعة')
                    ->relationship('fromFarm', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('to_farm_id')
                    ->label('إلى المزرعة')
                    ->relationship('toFarm', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
