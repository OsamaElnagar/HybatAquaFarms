<?php

namespace App\Filament\Resources\HarvestOperations\Tables;

use App\Filament\Resources\Drivers\RelationManagers\OrdersRelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items')->withSum('items', 'quantity')->withSum('items', 'total_weight'))
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable(),
                TextColumn::make('harvest.harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable(),
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable()
                    ->hiddenOn(OrdersRelationManager::class),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('عدد الأصناف')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),
                TextColumn::make('items_sum_quantity')
                    ->label('الصناديق (البكس)')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),
                TextColumn::make('items_sum_total_weight')
                    ->label('الوزن (كجم)')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),
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
            ->filters(
                [
                    SelectFilter::make('driver_id')
                        ->label('السائق')
                        ->relationship('driver', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->hiddenOn(OrdersRelationManager::class),
                    SelectFilter::make('trader_id')
                        ->label('التاجر')
                        ->relationship('trader', 'name')
                        ->searchable()
                        ->preload(),

                ]
            )
            ->defaultSort('date', 'desc');
    }
}
