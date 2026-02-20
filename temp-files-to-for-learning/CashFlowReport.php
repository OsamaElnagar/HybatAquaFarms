<?php

namespace App\Filament\Pages;

use App\Enums\PurchaseStatus;
use App\Enums\SaleStatus;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashFlowReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static \UnitEnum|string|null $navigationGroup = 'Reports';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $title = 'Cash Flow Report';

    protected string $view = 'filament.pages.cash-flow-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $sales = Sale::query()
                    ->selectRaw("
                        concat('sale_', id) as id,
                        sale_date as transaction_date,
                        'Sale' as type,
                        concat('Sale #', sale_number) as description,
                        total_amount as amount,
                        'success' as color
                    ")
                    ->where('status', SaleStatus::Completed);

                $purchases = Purchase::query()
                    ->selectRaw("
                        concat('purchase_', id) as id,
                        purchase_date as transaction_date,
                        'Purchase' as type,
                        concat('Purchase #', purchase_number) as description,
                        (total_amount * -1) as amount,
                        'danger' as color
                    ")
                    ->where('status', PurchaseStatus::Received);

                $expenses = Expense::query()
                    ->selectRaw("
                        concat('expense_', id) as id,
                        expense_date as transaction_date,
                        'Expense' as type,
                        description,
                        (amount * -1) as amount,
                        'warning' as color
                    ");

                // Filament requires an eloquent builder for many features, but we can return a Database Query Builder
                // if we are careful. However, the error says 'must be ... Eloquent\Builder'.

                // To fix this, we can trick it by using a model to start the query, but selecting from a subquery.
                // Or simply use a 'dummy' model query that uses 'from'.

                // Filament might append default scopes like SoftDeletingScope if the model has it.
                // Since we are using fromSub, those checks (like `sales.deleted_at is null`) will be applied to the subquery result alias `cash_flow_table`.
                // However, the error says `sales.deleted_at` which means it's trying to qualify the column with the model's table name.
                // This happens because we started the query with `Sale::query()`.

                // To avoid this, we should use `withoutGlobalScopes()`.

                // To avoid 'sales.id' or 'sales.deleted_at' errors, we need to ensure the query builder
                // uses the alias 'cash_flow_table' when qualifying columns.
                $model = new Sale;
                $model->setTable('cash_flow_table');

                return $model->newQuery()
                    ->withoutGlobalScopes()
                    ->fromSub(
                        $sales->toBase()->unionAll($purchases->toBase())->unionAll($expenses->toBase()),
                        'cash_flow_table'
                    );
            })
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn ($record) => $record->color),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money(decimalPlaces: 0)
                    ->sortable()
                    ->color(fn ($record) => $record->amount > 0 ? 'success' : 'danger')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()->money()->using(fn ($query) => $query->sum('amount'))),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Table $table) {
                        // For union query, get() returns Collection of Eloquent Models (Sale in this case, but with different attributes)
                        $records = $table->getQuery()->get();

                        $headers = ['Date', 'Type', 'Description', 'Amount'];
                        $rows = $records->map(fn ($record) => [
                            \Carbon\Carbon::parse($record->transaction_date)->format('Y-m-d h:i A'),
                            $record->type,
                            $record->description,
                            number_format($record->amount),
                        ])->toArray();

                        return app(\App\Services\PdfService::class)
                            ->generateReportPdf('Cash Flow Report', $headers, $rows)
                            ->stream('cash-flow-report.pdf');
                    }),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('transaction_date', 'desc');
    }
}
