<?php

namespace App\Filament\Resources\Farms\Tables;

use App\Enums\FarmStatus;
use App\Filament\Exports\FarmExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ExportBulkAction;
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
                    ->label('الوحدات')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('batches_count')
                    ->label('الدفعات')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('active_batches_count')
                    ->label('دفعات نشطة')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('total_current_stock')
                    ->label('المخزون الحالي')
                    ->numeric()
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(FarmExporter::class)
                    ->label('تصدير المزارع')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ExportBulkAction::make()
                        ->exporter(FarmExporter::class)
                        ->label('تصدير المحدد'),
                ]),
            ])
            ->defaultSort('name');
    }
}
