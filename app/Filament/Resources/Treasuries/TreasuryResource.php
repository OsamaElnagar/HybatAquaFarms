<?php

namespace App\Filament\Resources\Treasuries;

use App\Filament\Resources\Treasuries\Pages\ListTreasuries;
use App\Models\Treasury;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TreasuryResource extends Resource
{
    protected static ?string $model = Treasury::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'الخزينة';

    protected static ?string $modelLabel = 'الخزينة';

    protected static ?string $pluralModelLabel = 'الخزينة';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Treasury::recentCashLinesQuery(100))
            ->columns([
                TextColumn::make('journalEntry.entry_number')
                    ->label('رقم القيد')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('journalEntry.date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                TextColumn::make('account.code')
                    ->label('الحساب')
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('اسم الحساب')
                    ->sortable(),
                TextColumn::make('debit')
                    ->label('مدين')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('credit')
                    ->label('دائن')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('journalEntry.source_type')
                    ->label('المصدر')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('journalEntry.lines.farm', 'name'),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('journalEntry.date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreasuries::route('/'),
        ];
    }
}
