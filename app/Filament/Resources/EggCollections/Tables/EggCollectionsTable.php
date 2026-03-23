<?php

namespace App\Filament\Resources\EggCollections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EggCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('collection_number')
                    ->label('رقم التجميع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->searchable(),

                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->searchable(),

                TextColumn::make('collection_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_trays')
                    ->label('الصناديق')
                    ->numeric()
                    ->summarize(Sum::make()),

                TextColumn::make('total_eggs')
                    ->label('البيض')
                    ->numeric()
                    ->summarize(Sum::make()),

                TextColumn::make('quality_grade')
                    ->label('الجودة'),
            ])
            ->filters([
                //
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
