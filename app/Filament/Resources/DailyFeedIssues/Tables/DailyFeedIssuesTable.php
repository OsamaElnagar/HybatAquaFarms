<?php

namespace App\Filament\Resources\DailyFeedIssues\Tables;

use App\Models\FarmUnit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyFeedIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الحوض | الوحدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('feedItem.name')
                    ->badge()
                    ->label('صنف العلف')
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->badge()
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('دفعة الزريعة')
                    ->sortable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                // Nested filter: Farm -> Unit (units depend on selected farm)
                Filter::make('location')
                    ->label('الموقع')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->reactive(),
                        Select::make('unit_id')
                            ->label('الحوض | الوحدة')
                            ->options(function (Get $get): array {
                                $farmId = $get('farm_id');
                                $query = FarmUnit::query()->with('farm');
                                if ($farmId) {
                                    $query->where('farm_id', $farmId);
                                }

                                return $query
                                    ->orderBy('code')
                                    ->get()
                                    ->mapWithKeys(fn ($unit) => [
                                        $unit->id => ($farmId ? $unit->code : (($unit->farm?->name ?? '-').' - '.$unit->code)),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->disabled(fn (Get $get): bool => blank($get('farm_id'))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['farm_id'] ?? null, fn (Builder $q, $farmId) => $q->where('farm_id', $farmId))
                            ->when($data['unit_id'] ?? null, fn (Builder $q, $unitId) => $q->where('unit_id', $unitId));
                    }),

                // Additional helpful filters
                SelectFilter::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('feed_warehouse_id')
                    ->label('المخزن')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('batch_id')
                    ->label('دفعة الزريعة')
                    ->relationship('batch', 'batch_code', modifyQueryUsing: fn ($query) => $query->latest())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('recorded_by')
                    ->label('سجل بواسطة')
                    ->relationship('recordedBy', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date_range')
                    ->label('التاريخ')
                    ->schema([
                        DatePicker::make('from')->label('من')->native(false),
                        DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $from) => $q->whereDate('date', '>=', $from))
                            ->when($data['to'] ?? null, fn (Builder $q, $to) => $q->whereDate('date', '<=', $to));
                    }),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->defaultSort('date', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
