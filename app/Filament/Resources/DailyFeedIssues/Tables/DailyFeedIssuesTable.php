<?php

namespace App\Filament\Resources\DailyFeedIssues\Tables;

use App\Models\FarmUnit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Shreejan\ActionableColumn\Tables\Columns\ActionableColumn;

class DailyFeedIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            //         ->modifyQueryUsing(fn (Builder $query) => $query->with(['unit', 'feedItem', 'batch', 'warehouse', 'recordedBy']))
            ->columns([
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الحوض | الوحدة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                ActionableColumn::make('feedItem.name')
                    ->badge()
                    ->label('صنف العلف')
                    ->sortable()
                    ->actionIcon(Heroicon::PencilSquare)
                    ->actionIconColor('primary')
                    ->clickableColumn()
                    ->tapAction(
                        Action::make('changeFeedItem')
                            ->label('تغيير صنف العلف')
                            ->schema([
                                Select::make('feed_item_id')
                                    ->label('Feed Item')
                                    ->relationship('feedItem', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->fillForm(fn ($record) => [
                                'feed_item_id' => $record->feed_item_id,
                            ])
                            ->action(function ($record, array $data) {
                                $record->update($data);
                            })
                    ),
                ActionableColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->sortable()
                    ->actionIcon(Heroicon::PencilSquare)
                    ->actionIconColor('primary')
                    ->clickableColumn()
                    ->tapAction(
                        Action::make('changeWarehouse')
                            ->label('تغيير المخزن')
                            ->schema([
                                Select::make('feed_warehouse_id')
                                    ->label('Warehouse')
                                    ->relationship('warehouse', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->fillForm(fn ($record) => [
                                'feed_warehouse_id' => $record->feed_warehouse_id,
                            ])
                            ->action(function ($record, array $data) {
                                $record->update($data);
                            })
                    ),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                ActionableColumn::make('quantity')
                    ->badge()
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn ($record) => ' '.($record->feedItem?->unit_of_measure ?? ''))
                    ->summarize(Sum::make()->label('إجمالي الكمية'))
                    ->sortable()
                    ->actionIcon(Heroicon::PencilSquare)
                    ->actionIconColor('primary')
                    ->clickableColumn()
                    ->tapAction(
                        Action::make('changeQuantity')
                            ->label('تغيير الكمية')
                            ->schema([
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->fillForm(fn ($record) => [
                                'quantity' => $record->quantity,
                            ])
                            ->action(function ($record, array $data) {
                                $record->update($data);
                            })
                    ),
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
                            ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
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
                ReplicateAction::make()
                    ->label('نسخ')
                    ->excludeAttributes(['recorded_by', 'created_at', 'updated_at'])
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Set current user as recorded_by
                        $data['recorded_by'] = auth('web')->id();
                        // Set date to today by default, can be changed in form
                        $data['date'] = now()->format('Y-m-d');

                        return $data;
                    })
                    ->successRedirectUrl(fn () => route('filament.admin.resources.daily-feed-issues.index'))
                    ->successNotificationTitle('تم نسخ صرف العلف بنجاح'),
            ])
            ->defaultSort('date', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
