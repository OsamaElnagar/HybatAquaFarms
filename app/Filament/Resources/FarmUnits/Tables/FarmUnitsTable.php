<?php

namespace App\Filament\Resources\FarmUnits\Tables;

use App\Enums\FarmStatus;
use App\Enums\UnitType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FarmUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->addSelect([
                'total_feed_consumed' => \App\Models\DailyFeedIssue::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereIn('batch_id', function (\Illuminate\Database\Query\Builder $subQuery) {
                        $subQuery->select('batch_id')
                            ->from('batch_farm_unit')
                            ->whereColumn('farm_unit_id', 'farm_units.id');
                    }),
            ]))
            ->columns([
                TextColumn::make('farm.name')
                    ->searchable()
                    ->numeric()
                    ->sortable()
                    ->label('اسم المزرعة'),
                TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->label('الكود'),

                TextColumn::make('name')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ الاسم')
                    ->label('الاسم'),
                TextColumn::make('unit_type')
                    ->badge()
                    ->searchable()
                    ->label('نوع الوحدة'),
                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->label('السعة'),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->label('حالة الوحدة'),
                TextColumn::make('batches_count')
                    ->counts('batches')
                    ->label('عدد دفعات الزريعة')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('total_feed_consumed')
                    ->label('استهلاك العلف (كجم)')
                    ->numeric()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ التحديث'),
            ])
            ->filters([
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                SelectFilter::make('unit_type')
                    ->label('نوع الوحدة')
                    ->options(UnitType::class)
                    ->native(false),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(FarmStatus::class)
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}
