<?php

namespace App\Filament\Resources\Batches\Tables;

use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                // TextColumn::make('mortality_rate')
                //     ->label('معدل النفوق')
                //     ->formatStateUsing(function ($record) {
                //         if (! $record->initial_quantity || $record->initial_quantity == 0) {
                //             return '0%';
                //         }
                //         $mortality = $record->initial_quantity - $record->current_quantity;
                //         $rate = ($mortality / $record->initial_quantity) * 100;

                //         return number_format($rate).'%';
                //     })
                //     ->badge()
                //     ->color(function ($record) {
                //         if (! $record->initial_quantity || $record->initial_quantity == 0) {
                //             return 'gray';
                //         }
                //         $mortality = $record->initial_quantity - $record->current_quantity;
                //         $rate = ($mortality / $record->initial_quantity) * 100;

                //         return $rate > 10 ? 'danger' : ($rate > 5 ? 'warning' : 'success');
                //     })
                //     ->sortable(query: function ($query, string $direction) {
                //         return $query->orderByRaw("((initial_quantity - current_quantity) / NULLIF(initial_quantity, 0) * 100) {$direction}");
                //     })
                //     ->toggleable(),
                // TextColumn::make('days_since_entry')
                //     ->label('عدد الأيام')
                //     ->formatStateUsing(function ($record) {
                //         if (! $record->entry_date) {
                //             return '-';
                //         }
                //         $days = now()->diffInDays($record->entry_date);

                //         return $days.' يوم';
                //     })
                //     ->sortable(query: function ($query, string $direction) {
                //         return $query->orderBy('entry_date', $direction === 'asc' ? 'desc' : 'asc');
                //     })
                //     ->toggleable(),

                TextColumn::make('initial_weight_avg')
                    ->label('متوسط الوزن الأولي (جم)')
                    ->numeric()
                    ->suffix(' جم')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('current_weight_avg')
                    ->label('متوسط الوزن الحالي (جم)')
                    ->numeric()
                    ->suffix(' جم')
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('تكلفة الوحدة')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->sortable(),
                TextColumn::make('total_paid')
                    ->label('المدفوع')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('info')
                    ->sortable()
                    ->toggleable()
                    ->visible(
                        fn ($record) => $record &&
                            ($record->total_cost ?? 0) > 0,
                    ),
                TextColumn::make('outstanding_balance')
                    ->label('المتبقي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(
                        fn ($record) => $record &&
                        ($record->outstanding_balance ?? 0) > 0
                            ? 'danger'
                            : 'success',
                    )
                    ->sortable()
                    ->toggleable()
                    ->visible(
                        fn ($record) => $record &&
                            ($record->total_cost ?? 0) > 0,
                    ),
                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if (
                            ! $record ||
                            ! $record->total_cost ||
                            $record->total_cost <= 0
                        ) {
                            return 'لا يوجد تكلفة';
                        }
                        if ($record->is_fully_paid) {
                            return 'مدفوع بالكامل';
                        }
                        $paidPercentage =
                            ($record->total_paid / $record->total_cost) * 100;

                        return number_format($paidPercentage, 1).'% مدفوع';
                    })
                    ->color(
                        fn ($record) => $record
                            ? $record->payment_status
                            : 'gray',
                    )
                    ->sortable()
                    ->toggleable()
                    ->visible(
                        fn ($record) => $record &&
                            ($record->total_cost ?? 0) > 0,
                    ),
                TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()

                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_cycle_closed')
                    ->label('حالة الدورة')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state ? 'مقفلة' : 'مفتوحة',
                    )
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('net_profit')
                    ->label('صافي الربح')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(
                        fn ($record) => $record && $record->net_profit >= 0
                            ? 'success'
                            : 'danger',
                    )
                    ->sortable()
                    ->toggleable()
                    ->visible(
                        fn ($record) => $record && $record->is_cycle_closed,
                    ),
                TextColumn::make('profit_margin')
                    ->label('هامش الربح')
                    ->formatStateUsing(
                        fn ($record) => $record
                            ? number_format($record->profit_margin, 1).'%'
                            : '0%',
                    )
                    ->badge()
                    ->color(
                        fn ($record) => $record && $record->profit_margin >= 20
                            ? 'success'
                            : ($record && $record->profit_margin >= 0
                                ? 'warning'
                                : 'danger'),
                    )
                    ->sortable()
                    ->toggleable()
                    ->visible(
                        fn ($record) => $record && $record->is_cycle_closed,
                    ),
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
                    ->options(BatchSource::class)
                    ->native(false),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'fully_paid' => 'مدفوع بالكامل',
                        'partially_paid' => 'مدفوع جزئياً',
                        'unpaid' => 'غير مدفوع',
                        'no_cost' => 'لا يوجد تكلفة',
                    ])
                    ->query(function ($query, array $data) {
                        if (! isset($data['value']) || $data['value'] === null) {
                            return;
                        }

                        return match ($data['value']) {
                            'fully_paid' => $query
                                ->whereRaw('total_cost > 0')
                                ->whereRaw(
                                    '(SELECT COALESCE(SUM(amount), 0) FROM batch_payments WHERE batch_payments.batch_id = batches.id) >= total_cost',
                                ),
                            'partially_paid' => $query
                                ->whereRaw('total_cost > 0')
                                ->whereRaw(
                                    '(SELECT COALESCE(SUM(amount), 0) FROM batch_payments WHERE batch_payments.batch_id = batches.id) > 0',
                                )
                                ->whereRaw(
                                    '(SELECT COALESCE(SUM(amount), 0) FROM batch_payments WHERE batch_payments.batch_id = batches.id) < total_cost',
                                ),
                            'unpaid' => $query
                                ->whereRaw('total_cost > 0')
                                ->whereRaw(
                                    '(SELECT COALESCE(SUM(amount), 0) FROM batch_payments WHERE batch_payments.batch_id = batches.id) = 0',
                                ),
                            'no_cost' => $query->where(function ($q) {
                                $q->whereNull('total_cost')->orWhere(
                                    'total_cost',
                                    '<=',
                                    0,
                                );
                            }),
                            default => $query,
                        };
                    })
                    ->native(false),
                SelectFilter::make('is_cycle_closed')
                    ->label('حالة إقفال الدورة')
                    ->options([
                        1 => 'مقفلة',
                        0 => 'مفتوحة',
                    ])
                    ->native(false),
            ])
            ->defaultSort('entry_date', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
