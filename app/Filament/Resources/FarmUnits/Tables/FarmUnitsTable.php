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

class FarmUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('farm.name')
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
