<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HarvestBoxesRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestBoxes';

    protected static ?string $title = 'صناديق الحصاد';

    protected static ?string $recordTitleAttribute = 'box_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('harvest.harvest_number')
                    ->label('الحصاد')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('box_number')
                    ->label('رقم الصندوق')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('classification')
                    ->label('التصنيف')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn ($state) => match ($state) {
                        'جامبو' => 'success',
                        'بلطي' => 'info',
                        'نمرة 1' => 'warning',
                        'نمرة 2' => 'warning',
                        'نمرة 3' => 'gray',
                        'نمرة 4' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->numeric(decimalPlaces: 2)),

                TextColumn::make('fish_count')
                    ->label('العدد')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')),

                TextColumn::make('average_fish_weight')
                    ->label('متوسط الوزن (جم)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_sold')
                    ->label('مباع')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('subtotal')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP'))
                    ->placeholder('—')
                    ->weight('bold'),

                TextColumn::make('sold_at')
                    ->label('تاريخ البيع')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_sold')
                    ->label('حالة البيع')
                    ->placeholder('الكل')
                    ->trueLabel('مباع')
                    ->falseLabel('متاح')
                    ->native(false),

                SelectFilter::make('classification')
                    ->label('التصنيف')
                    ->options([
                        'بلطي' => 'بلطي',
                        'نمرة 1' => 'نمرة 1',
                        'نمرة 2' => 'نمرة 2',
                        'نمرة 3' => 'نمرة 3',
                        'نمرة 4' => 'نمرة 4',
                        'جامبو' => 'جامبو',
                        'خرط' => 'خرط',
                    ])
                    ->native(false),

                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->headerActions([
                // Create action will be added later
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('box_number', 'asc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

        ]);
    }
}
