<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\HarvestStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
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
                TextColumn::make('salesOrder.order_number')
                    ->label('رقم العملية')
                    ->sortable()
                    ->toggleable(),
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
}
