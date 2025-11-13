<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

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
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->sortable(),
                TextColumn::make('boxes_count')
                    ->label('الصناديق')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع'),
                    ]),
                TextColumn::make('total_weight')
                    ->label('الوزن الإجمالي')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كجم')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 3)
                            ->suffix(' كجم'),
                    ]),
                TextColumn::make('total_quantity')
                    ->label('العدد')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع'),
                    ]),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof HarvestStatus ? $state->getLabel() : $state)
                    ->color(fn ($state) => $state instanceof HarvestStatus ? $state->getColor() : 'gray')
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
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('harvest_date', 'desc');
    }
}
