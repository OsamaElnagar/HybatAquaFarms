<?php

namespace App\Filament\Resources\Farms\Tables;

use App\Enums\FarmStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FarmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('units_count')
                    ->counts('units')
                    ->label('الوحدات')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('batches_count')
                    ->counts('batches')
                    ->label('الدفعات')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('active_batches')
                    ->label('دفعات نشطة')
                    ->state(fn ($record) => $record->active_batches_count)
                    ->badge()
                    ->color('info')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->withCount([
                            'batches as active_batches_count' => fn ($q) => $q->where('status', 'active'),
                        ])->orderBy('active_batches_count', $direction);
                    }),
                TextColumn::make('current_stock')
                    ->label('المخزون الحالي')
                    ->state(fn ($record) => number_format($record->total_current_stock))
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('manager.name')
                    ->label('المدير')
                    ->placeholder('غير محدد')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('الموقع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('size')
                    ->label('المساحة')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('established_date')
                    ->label('تاريخ التأسيس')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->options(FarmStatus::class)
                    ->native(false),
                SelectFilter::make('manager_id')
                    ->label('المدير')
                    ->relationship('manager', 'name'),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('name');
    }
}
