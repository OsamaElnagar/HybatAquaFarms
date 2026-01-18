<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TreasuryTransactions extends TableWidget
{
    protected static ?string $heading = 'تحركات الخزنة الأخيرة';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\JournalLine::query()
                    ->whereHas('account', fn ($q) => $q->where('is_treasury', true))
                    ->with(['account', 'journalEntry'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('journalEntry.date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('الحساب')
                    ->searchable(),
                TextColumn::make('debit')
                    ->label('وارد (+)')
                    ->money('EGP', decimalPlaces: 0, locale: 'en')
                    ->color('success')
                    ->hidden(fn ($record) => $record && $record->debit <= 0),
                TextColumn::make('credit')
                    ->label('صادر (-)')
                    ->money('EGP', decimalPlaces: 0, locale: 'en')
                    ->color('danger')
                    ->hidden(fn ($record) => $record && $record->credit <= 0),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('journalEntry.source_type')
                    ->label('المصدر')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('account_id')
                    ->label('تصفية بالحساب')
                    ->relationship('account', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('is_treasury', true)),
            ]);
    }
}
