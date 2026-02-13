<?php

namespace App\Filament\Resources\PettyCashes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PettyCashesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العهدة')
                    ->searchable(),

                TextColumn::make('custodian.name')
                    ->label('المستأمن')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('current_balance')
                    ->label('الرصيد الحالي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->current_balance > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('opening_date')
                    ->label('تاريخ الافتتاح')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
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
                //
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
