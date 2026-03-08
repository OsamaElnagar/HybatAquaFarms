<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\BatchStatus;
use App\Filament\Resources\Batches\Schemas\BatchForm;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'دفعات الزريعة';

    public function form(Schema $schema): Schema
    {
        return BatchForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_code')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['units', 'species']))
            ->columns([
                TextColumn::make('batch_code')
                    ->label('كود الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('units.code')
                    ->label('الوحدات')
                    ->badge()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('fish.species.name')
                    ->label('النوع')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('entry_date')
                    ->label('تاريخ الإدخال')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('current_quantity')
                    ->label('الكمية الحالية')
                    ->numeric(locale: 'en')
                    ->color(fn ($record) => $record->current_quantity < $record->initial_quantity ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(BatchStatus::class)
                    ->native(false),
                SelectFilter::make('units')
                    ->label('الوحدة')
                    ->relationship('units', 'code'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
