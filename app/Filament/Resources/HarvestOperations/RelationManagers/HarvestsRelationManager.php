<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Enums\HarvestStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HarvestsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvests';

    protected static ?string $title = 'جلسات الحصاد اليومية';

    protected static ?string $recordTitleAttribute = 'harvest_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('harvest_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('shift')
                    ->label('الفترة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'morning' => 'صباحي',
                        'afternoon' => 'ظهري',
                        'night' => 'مسائي',
                        default => '—'
                    })
                    ->color(fn ($state) => match ($state) {
                        'morning' => 'warning',
                        'afternoon' => 'info',
                        'night' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('total_boxes')
                    ->label('الصناديق')
                    ->numeric(),

                TextColumn::make('total_weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('total_quantity')
                    ->label('عدد الأسماك')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('soldBoxesCount')
                    ->label('مباع')
                    ->numeric()
                    ->color('success'),

                TextColumn::make('recordedBy.name')
                    ->label('المسجل')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(HarvestStatus::class)
                    ->native(false),

                SelectFilter::make('shift')
                    ->label('الفترة')
                    ->options([
                        'morning' => 'صباحي',
                        'afternoon' => 'ظهري',
                        'night' => 'مسائي',
                    ])
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('تسجيل حصاد جديد'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('harvest_date', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

        ]);
    }
}
