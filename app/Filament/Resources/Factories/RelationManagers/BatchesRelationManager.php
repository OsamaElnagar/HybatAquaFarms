<?php

namespace App\Filament\Resources\Factories\RelationManagers;

use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batchFish';

    protected static ?string $title = 'دفعات الزريعة الموردة';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['batch.farm', 'species']))
            ->columns([
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة المجمعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch.farm.name')
                    ->label('المزرعة')
                    ->sortable(),
                TextColumn::make('species.name')
                    ->label('الصنف')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية الموردة')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('سعر الوحدة')
                    ->money('EGP', locale: 'en', decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('total_cost')
                    ->label('التكلفة (لهذا المورد)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('الإجمالي')
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                // TextColumn::make('batch.entry_date')
                //     ->label('تاريخ الإدخال (للدفعة كاملة)')
                //     ->date('Y-m-d')
                //     ->sortable(),

                // TextColumn::make('batch.status')
                //     ->label('حالة الدفعة المجمعة')
                //     ->badge()
                //     ->sortable(),
            ])
            ->filters([
                SelectFilter::make('batch.status')
                    ->label('حالة الدفعة')
                    ->options(BatchStatus::class)
                    ->native(false),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
