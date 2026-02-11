<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\PettyTransacionType;
use App\Filament\Resources\PettyCashTransactions\Schemas\PettyCashTransactionForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PettyCashTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pettyCashTransactions';

    protected static ?string $title = 'معاملات العهدة';

    public function form(Schema $schema): Schema
    {
        return PettyCashTransactionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->modifyQueryUsing(fn ($query) => $query->with(['pettyCash', 'expenseCategory', 'recordedBy']))
            ->columns([
                TextColumn::make('pettyCash.name')
                    ->label('العهدة')
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('petty_cash_id')
                    ->label('العهدة')
                    ->relationship('pettyCash', 'name'),
                SelectFilter::make('direction')
                    ->label('الاتجاه | النوع')
                    ->options(PettyTransacionType::class),
                SelectFilter::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name'),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->label('من تاريخ'),
                        DatePicker::make('date_to')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'] ?? null, fn ($q, $date) => $q->where('date', '>=', $date))
                            ->when($data['date_to'] ?? null, fn ($q, $date) => $q->where('date', '<=', $date));
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة معاملة')
                    ->mutateDataUsing(function (array $data): array {
                        $data['recorded_by'] = Auth::id();

                        return $data;
                    }),
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
