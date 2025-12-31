<?php

namespace App\Filament\Resources\Boxes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BoxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('species.name')
                    ->label('النوع')
                    ->numeric(),
                TextColumn::make('max_weight')
                    ->label('الوزن الأقصى')
                    ->numeric(),
                TextColumn::make('class')
                    ->label('التصنيف')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('الفئة')
                    ->searchable(),
                TextColumn::make('class_total_weight')
                    ->label('وزن الفئة (شامل الاسماك)')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()

                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()

                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('species_id')
                    ->label('النوع')
                    ->relationship('species', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
