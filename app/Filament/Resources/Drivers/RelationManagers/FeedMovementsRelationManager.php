<?php

namespace App\Filament\Resources\Drivers\RelationManagers;

use App\Enums\FeedMovementType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'feedMovements';

    protected static ?string $title = 'حركات الأعلاف المنقولة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->modifyQueryUsing(
                fn ($query) => $query->where('movement_type', FeedMovementType::In),
            )
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('المصنع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toWarehouse.name')
                    ->label('المستودع المستلم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->sortable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('to_warehouse_id')
                    ->label('المستودع المستلم')
                    ->relationship('toWarehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([])
            ->headerActions([])
            ->toolbarActions([]);
    }
}

