<?php

namespace App\Filament\Resources\ExternalCalculations\Tables;

use App\Enums\ExternalCalculationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExternalCalculationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->color(fn ($state) => $state?->getColor()),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('treasuryAccount.name')
                    ->label('الخزينة')
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('الحساب المقابل')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->type?->value === ExternalCalculationType::Receipt->value ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->label('المرجع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class),
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
