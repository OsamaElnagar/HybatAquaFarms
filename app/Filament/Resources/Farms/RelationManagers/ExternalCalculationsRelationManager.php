<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\ExternalCalculationType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ExternalCalculationsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalCalculations';

    protected static ?string $title = 'حسابات خارجية';

    protected static ?string $modelLabel = 'معاملة';

    protected static ?string $pluralModelLabel = 'معاملات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->modifyQueryUsing(fn ($query) => $query->with(['treasuryAccount', 'account', 'externalCalculation']))
            ->columns([
                TextColumn::make('externalCalculation.name')
                    ->label('الحساب')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('treasuryAccount.name')
                    ->label('الخزينة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('account.name')
                    ->label('الحساب المقابل')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->type === ExternalCalculationType::Receipt ? 'success' : 'danger')
                    ->sortable()
                    ->summarize([
                        Summarizer::make()
                            ->label('المقبوضات')
                            ->query(fn ($query) => $query->where('type', ExternalCalculationType::Receipt))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('المدفوعات')
                            ->query(fn ($query) => $query->where('type', ExternalCalculationType::Payment))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('الصافي')
                            ->using(fn ($query) => $query->sum(DB::raw("CASE WHEN type = 'receipt' THEN amount ELSE -amount END")))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class),
            ])
            ->defaultSort('date', 'desc');
    }
}
