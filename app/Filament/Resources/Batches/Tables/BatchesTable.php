<?php

namespace App\Filament\Resources\Batches\Tables;

use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_code')
                    ->label('كود الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('species.name')
                    ->label('النوع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('مصنع التفريخ')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('entry_date')
                    ->label('تاريخ الإدخال')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('initial_quantity')
                    ->label('الكمية الأولية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_quantity')
                    ->label('الكمية الحالية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mortality_rate')
                    ->label('معدل النفوق')
                    ->formatStateUsing(function ($record) {
                        if (! $record->initial_quantity || $record->initial_quantity == 0) {
                            return '0%';
                        }
                        $mortality = $record->initial_quantity - $record->current_quantity;
                        $rate = ($mortality / $record->initial_quantity) * 100;

                        return number_format($rate, 2).'%';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (! $record->initial_quantity || $record->initial_quantity == 0) {
                            return 'gray';
                        }
                        $mortality = $record->initial_quantity - $record->current_quantity;
                        $rate = ($mortality / $record->initial_quantity) * 100;

                        return $rate > 10 ? 'danger' : ($rate > 5 ? 'warning' : 'success');
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw("((initial_quantity - current_quantity) / NULLIF(initial_quantity, 0) * 100) {$direction}");
                    })
                    ->toggleable(),
                TextColumn::make('days_since_entry')
                    ->label('عدد الأيام')
                    ->formatStateUsing(function ($record) {
                        if (! $record->entry_date) {
                            return '-';
                        }
                        $days = now()->diffInDays($record->entry_date);

                        return $days.' يوم';
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('entry_date', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->toggleable(),
                TextColumn::make('initial_weight_avg')
                    ->label('متوسط الوزن الأولي (جم)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' جم')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('current_weight_avg')
                    ->label('متوسط الوزن الحالي (جم)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' جم')
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('تكلفة الوحدة')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('source')
                    ->label('المصدر')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BatchStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state instanceof BatchStatus ? $state->value : $state) {
                        'active' => 'success',
                        'harvested' => 'warning',
                        'depleted' => 'gray',
                        default => 'gray',
                    })
                    ->searchable()
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
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(BatchStatus::class)
                    ->native(false),
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('species_id')
                    ->label('النوع')
                    ->relationship('species', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('factory_id')
                    ->label('مصنع التفريخ')
                    ->relationship('factory', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('source')
                    ->label('المصدر')
                    ->options([
                        'hatchery' => 'مفرخة',
                        'transfer' => 'نقل',
                        'purchase' => 'شراء',
                    ])
                    ->native(false),
            ])
            ->defaultSort('entry_date', 'desc')
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
