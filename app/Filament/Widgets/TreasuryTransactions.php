<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TreasuryTransactions extends TableWidget
{
    protected static ?string $heading = 'تحركات الخزينة الأخيرة';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\JournalLine::query()
                    ->whereHas('account', fn ($q) => $q->where('is_treasury', true))
                    ->with(['account', 'journalEntry.source'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('journalEntry.date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('account.name')
                    ->label('الحساب')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('debit')
                    ->label('وارد (+)')
                    ->money('EGP')
                    ->color('success')
                    ->hidden(fn ($record) => $record && $record->debit <= 0),
                \Filament\Tables\Columns\TextColumn::make('credit')
                    ->label('صادر (-)')
                    ->money('EGP')
                    ->color('danger')
                    ->hidden(fn ($record) => $record && $record->credit <= 0),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->wrap(),
                \Filament\Tables\Columns\TextColumn::make('journalEntry.source_type')
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
