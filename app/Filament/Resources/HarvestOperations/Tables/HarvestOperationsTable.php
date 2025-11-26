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
                    ->description(fn ($record) => $record->batch->species->name ?? ''),

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

                TextColumn::make('days_running')
                    ->label('الأيام')
                    ->suffix(' يوم')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_boxes')
                    ->label('الصناديق')
                    ->numeric()
                    ->alignCenter()
                    ->description(fn ($record) => number_format($record->total_weight, 1).' كجم'),

                TextColumn::make('sold_boxes_count')
                    ->label('مباع')
                    ->numeric()
                    ->alignCenter()
                    ->color('success'),

                TextColumn::make('unsold_boxes_count')
                    ->label('متاح')
                    ->numeric()
                    ->alignCenter()
                    ->color('warning'),

                TextColumn::make('total_revenue')
                    ->label('الإيرادات')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),

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
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload()
                    ->native(false),
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
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
