<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Filament\Resources\Factories\Actions\MakePaymentAction;
use App\Filament\Resources\Factories\Actions\OpenNewStatementAction;
use App\Filament\Resources\Factories\FactoryResource;
use App\Filament\Resources\Factories\Widgets\FactoryStatsWidget;
use App\Models\FactoryStatement;
use App\Models\JournalLine;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatementOfAccount extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = FactoryResource::class;

    protected string $view = 'filament.resources.factories.pages.statement-of-account';

    protected static ?string $title = 'كشف حساب';

    /** The active statement ID to filter by, null = show all history */
    public ?int $activeStatementId = null;

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
        ];

        if (request()->query('statement_id')) {
            $breadcrumbs[$resource::getUrl('statements', ['record' => $this->getRecord()])] = 'سجل الكشوفات';
        }

        $breadcrumbs['#'] = static::$title;

        return $breadcrumbs;
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Check if a specific statement was requested via URL
        $requestedStatementId = request()->query('statement_id');

        if ($requestedStatementId) {
            $this->activeStatementId = (int) $requestedStatementId;
        } else {
            // Default to the active open session
            $this->activeStatementId = $this->record->activeStatement?->id;
        }
    }

    public function getHeaderActions(): array
    {
        return [
            OpenNewStatementAction::make(),

            Action::make('statementsHistory')
                ->label('سجل الكشوفات')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn () => FactoryResource::getUrl('statements', ['record' => $this->record])),

            Action::make('viewAllHistory')
                ->label($this->activeStatementId ? 'عرض كل التاريخ' : 'عرض الكشف الحالي فقط')
                ->icon($this->activeStatementId ? 'heroicon-o-clock' : 'heroicon-o-document-text')
                ->color('gray')
                ->action(function () {
                    $this->activeStatementId = $this->activeStatementId
                        ? null
                        : $this->record->activeStatement?->id;
                }),
            MakePaymentAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FactoryStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JournalLine::query()
                    ->where('account_id', $this->record->account_id)
                    ->when(
                        $this->activeStatementId,
                        fn (Builder $q) => $q->whereHas(
                            'journalEntry',
                            fn (Builder $inner) => $inner->where('factory_statement_id', $this->activeStatementId)
                        )
                    )
                    ->with('journalEntry')
            )
            ->columns([
                TextColumn::make('journalEntry.date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('journalEntry.entry_number')
                    ->label('رقم القيد')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('البيان')
                    ->wrap()
                    ->getStateUsing(fn (JournalLine $record) => $record->description ?: $record->journalEntry->description),
                TextColumn::make('credit')
                    ->label('دائن (له)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('danger')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي المشتريات')),
                TextColumn::make('debit')
                    ->label('مدين (صرفنا له)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي المدفوعات')),
            ])
            ->defaultSort('journalEntry.date', 'desc')
            ->filters([
                SelectFilter::make('statement')
                    ->label('الكشف / الجلسة')
                    ->options(fn () => FactoryStatement::where('factory_id', $this->record->id)
                        ->orderByDesc('opened_at')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => ($s->title ? $s->title.' | ' : '').$s->opened_at->format('Y-m-d').' | '.$s->status->getLabel()])
                        ->toArray())
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('journalEntry', fn ($q) => $q->where('factory_statement_id', $data['value']))
                        : $query),
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_from')->label('من تاريخ'),
                        DatePicker::make('date_to')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '>=', $date)),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $date)),
                            );
                    }),
            ]);
    }

    public function setActiveStatementId(int $id): void
    {
        $this->activeStatementId = $id;
    }
}
