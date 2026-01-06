<?php

namespace App\Filament\Resources\HarvestOperations\Tables;

use App\Enums\HarvestOperationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HarvestOperationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operation_number')
                    ->label('رقم العملية')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->batch?->species?->name ?? ''),

                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('مستمر'),

                TextColumn::make('total_boxes')
                    ->label('إجمالي الصناديق')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('total_weight')
                    ->label('إجمالي الوزن')
                    ->numeric(2)
                    ->suffix(' كجم')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(HarvestOperationStatus::class)
                    ->native(false),

                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('batch_id')
                    ->label('الدفعة')
                    ->relationship('batch', 'batch_code', modifyQueryUsing: fn ($query) => $query->latest())
                    ->searchable()
                    ->preload()
                    ->native(false),
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
            ->defaultSort('created_at', 'desc');
        // ->poll('30s') // Auto-refresh every 30 seconds
    }
}
