<?php

namespace App\Filament\Resources\PettyCashTransactions\Tables;

use App\Enums\PettyTransacionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->numeric()
                    ->suffix(' EGP ')
                    ->sortable(),
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
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('petty_cash_id')
                    ->label('العهدة')
                    ->relationship('pettyCash', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('direction')
                    ->label('الاتجاه | النوع')
                    ->options(PettyTransacionType::class),
                SelectFilter::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('من تاريخ'),
                        DatePicker::make('date_to')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
