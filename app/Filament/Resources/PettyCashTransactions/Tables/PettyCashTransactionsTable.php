<?php

namespace App\Filament\Resources\PettyCashTransactions\Tables;

use App\Enums\PettyTransacionType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Shreejan\ActionableColumn\Tables\Columns\ActionableColumn;

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
                ActionableColumn::make('expenseCategory.name')
                    ->label('نوع المصروف')
                    ->sortable()
                    ->toggleable()
                    ->actionIcon(Heroicon::PencilSquare)
                    ->actionIconColor('primary')
                    ->clickableColumn()
                    ->tapAction(
                        Action::make('changeCategory')
                            ->label('Change Category')
                            ->schema([
                                Select::make('expense_category_id')
                                    ->label('Expense Category')
                                    ->relationship('expenseCategory', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->fillForm(fn ($record) => [
                                'expense_category_id' => $record->expense_category_id,
                            ])
                            ->action(function ($record, array $data) {
                                $record->update($data);
                            })
                    ),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المقبوضات (قبض)')
                            ->query(fn ($query) => $query->where('direction', PettyTransacionType::IN))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المدفوعات (صرف)')
                            ->query(fn ($query) => $query->where('direction', PettyTransacionType::OUT))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        \Filament\Tables\Columns\Summarizers\Summarizer::make()
                            ->label('صافي الرصيد')
                            ->using(fn ($query) => $query->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN direction = 'in' THEN amount ELSE -amount END")))
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
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('من تاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                        DatePicker::make('date_to')
                            ->label('إلى تاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false),
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
                ReplicateAction::make()->label('استنساخ ')->requiresConfirmation(false),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
