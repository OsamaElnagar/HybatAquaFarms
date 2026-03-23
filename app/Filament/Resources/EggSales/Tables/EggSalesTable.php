<?php

namespace App\Filament\Resources\EggSales\Tables;

use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EggSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->placeholder('نقدي'),

                TextColumn::make('trays_sold')
                    ->label('الصناديق')
                    ->badge()
                    ->color(Color::Blue)
                    ->numeric(decimalPlaces: 0)
                    ->summarize(Sum::make()),

                TextColumn::make('total_eggs')
                    ->label('البيض')
                    ->badge()
                    ->color(Color::Blue)
                    ->numeric(decimalPlaces: 0)
                    ->summarize(Sum::make()),

                TextColumn::make('net_amount')
                    ->label('المبلغ')
                    ->sortable()
                    ->color(Color::Green)
                    ->money('EGP', decimalPlaces: 0, locale: 'en')
                    ->badge()
                    ->color(Color::Green)
                    ->summarize(Sum::make()->money('EGP', decimalPlaces: 0, locale: 'en')),
                TextColumn::make('sale_number')
                    ->label('رقم البيع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('eggCollection.collection_number')
                    ->label('التجميع')
                    ->searchable(),
            ])
            ->filters([
                DateRangeFilter::make('sale_date'),
            ])
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
