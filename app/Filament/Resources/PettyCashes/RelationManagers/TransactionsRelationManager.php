<?php

namespace App\Filament\Resources\PettyCashes\RelationManagers;

use App\Enums\PettyTransacionType;
use App\Filament\Resources\PettyCashes\Actions\BulkTransactionsAction;
use App\Filament\Resources\PettyCashTransactions\Schemas\PettyCashTransactionForm;
use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'معاملات العهدة';

    protected static ?string $modelLabel = 'معاملة جديدة';

    protected static ?string $pluralModelLabel = 'معاملات العهدة';

    public function form(Schema $schema): Schema
    {
        return PettyCashTransactionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
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
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->direction === PettyTransacionType::OUT ? 'danger' : 'success')
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
                    ->wrap(),
                TextColumn::make('voucher.voucher_number')
                    ->label('رقم السند')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('النوع')
                    ->options(PettyTransacionType::class),
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name')
                    ->searchable()
                    ->preload(),
                DateRangeFilter::make('date'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة معاملة')
                    ->mutateDataUsing(function (array $data): array {
                        $data['recorded_by'] = Auth::id();
                        $data['petty_cash_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
                BulkTransactionsAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
