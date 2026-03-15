<?php

namespace App\Filament\Resources\Traders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'أردرات التاجر';

    protected static ?string $modelLabel = 'أردر';

    protected static ?string $pluralModelLabel = 'أردرات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items')->withSum('items', 'quantity')->withSum('items', 'total_weight'))
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harvestOperation.operation_number')
                    ->label('رقم الحصاد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harvest.harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('عدد الأصناف')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),
                TextColumn::make('items_sum_quantity')
                    ->label('الصناديق (البكس)')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),
                TextColumn::make('items_sum_total_weight')
                    ->label('الوزن (كجم)')
                    ->numeric(locale: 'en')
                    ->summarize(Sum::make()->numeric(locale: 'en'))
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([])
            ->defaultSort('date', 'desc');
    }
}
