<?php

namespace App\Filament\Resources\PettyCashTransactions\Tables;

use App\Enums\PettyTransacionType;
use App\Filament\Exports\PettyCashTransactionExporter;
use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PettyCashTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pettyCash.name')
                    ->label('العهدة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('direction')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('expenseCategory.name')
                    ->label('نوع المصروف')
                    ->sortable()
                    ->description(fn ($record) => $record->expenseCategory?->code === 'WORKER_SALARY' ? "{$record->employee?->name}" : null)
                    ->toggleable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->summarize([
                        Summarizer::make()
                            ->label('المقبوضات (قبض)')
                            ->query(fn ($query) => $query->where('direction', PettyTransacionType::IN))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('المدفوعات (صرف)')
                            ->query(fn ($query) => $query->where('direction', PettyTransacionType::OUT))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('صافي الرصيد')
                            ->using(fn ($query) => $query->sum(DB::raw("CASE WHEN direction = 'in' THEN amount ELSE -amount END")))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('voucher.voucher_number')
                    ->label('رقم السند')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('petty_cash_id')
                    ->label('العهدة')
                    ->relationship('pettyCash', 'name')->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('direction')
                    ->label('الاتجاه | النوع')
                    ->options(PettyTransacionType::class),
                SelectFilter::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                DateRangeFilter::make('date'),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),

                // ReplicateAction::make()->label('استنساخ ')->requiresConfirmation(false),

            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(PettyCashTransactionExporter::class),
                // ]),
            ]);
    }
}
