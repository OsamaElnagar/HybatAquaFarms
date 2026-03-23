<?php

namespace App\Filament\Resources\Traders\Pages;

use App\Filament\Resources\Traders\Actions\GiveCashAction;
use App\Filament\Resources\Traders\Actions\OpenNewStatementAction;
use App\Filament\Resources\Traders\Actions\ReceivePaymentAction;
use App\Filament\Resources\Traders\TraderResource;
use App\Filament\Resources\Traders\Widgets\TraderStatsWidget;
use App\Filament\Tables\Filters\DateRangeFilter;
use App\Models\JournalLine;
use App\Models\TraderStatement;
use App\Services\PdfService;
use Carbon\Carbon;
use Filament\Actions\Action;
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

    protected static string $resource = TraderResource::class;

    protected string $view = 'filament.resources.traders.pages.statement-of-account';

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
            Action::make('exportPdf')
                ->label('تصدير PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $this->exportStatementPdf();
                }),

            OpenNewStatementAction::make(),

            Action::make('statementsHistory')
                ->label('سجل الكشوفات')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn () => TraderResource::getUrl('statements', ['record' => $this->record])),

            Action::make('viewAllHistory')
                ->label($this->activeStatementId ? 'عرض كل التاريخ' : 'عرض الكشف الحالي فقط')
                ->icon($this->activeStatementId ? 'heroicon-o-clock' : 'heroicon-o-document-text')
                ->color('gray')
                ->action(function () {
                    $this->activeStatementId = $this->activeStatementId
                        ? null
                        : $this->record->activeStatement?->id;
                }),
            ReceivePaymentAction::make(),
            GiveCashAction::make(),
        ];
    }

    protected function exportStatementPdf(): void
    {
        $statement = $this->activeStatementId
            ? TraderStatement::with('harvestOperations')->find($this->activeStatementId)
            : null;

        $journalLines = JournalLine::query()
            ->where('account_id', $this->record->account_id)
            ->when(
                $this->activeStatementId,
                fn (Builder $q) => $q->whereHas(
                    'journalEntry',
                    fn (Builder $inner) => $inner->where('trader_statement_id', $this->activeStatementId)
                )
            )
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->select('journal_lines.*', 'journal_entries.date as je_date', 'journal_entries.entry_number', 'journal_entries.description as je_description')
            ->orderBy('je_date')
            ->orderBy('journal_entries.id')
            ->get();

        $entries = $journalLines->map(fn ($line) => [
            'date' => $line->je_date ? Carbon::parse($line->je_date)->format('Y-m-d') : '-',
            'entry_number' => $line->entry_number ?? '-',
            'description' => $line->description ?: $line->je_description ?? '-',
            'debit' => (float) $line->debit,
            'credit' => (float) $line->credit,
        ])->toArray();

        $statementData = [
            'title' => $statement?->title ?? 'كشف حساب - السجل الكامل',
            'status' => $statement?->status?->value ?? 'open',
            'opened_at' => $statement?->opened_at?->format('Y-m-d') ?? '-',
            'closed_at' => $statement?->closed_at?->format('Y-m-d') ?? null,
            'opening_balance' => (float) ($statement?->opening_balance ?? 0),
            'closing_balance' => (float) ($statement?->net_balance ?? $this->record->outstanding_balance),
            'notes' => $statement?->notes,
        ];

        $pdf = (new PdfService)->generateStatementPdf(
            'trader',
            $this->record->name,
            $statementData,
            $entries
        );

        $filename = 'statement-trader-'.$this->record->id.'-'.now()->format('Y-m-d').'.pdf';

        $pdf->stream($filename);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TraderStatsWidget::class,
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
                            fn (Builder $inner) => $inner->where('trader_statement_id', $this->activeStatementId)
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
                TextColumn::make('debit')
                    ->label('مدين (عليه)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('danger')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي المدين')),
                TextColumn::make('credit')
                    ->label('دائن (له)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي الدائن')),
            ])
            ->defaultSort('journalEntry.date', 'desc')
            ->filters([
                SelectFilter::make('statement')
                    ->label('الكشف / الجلسة')
                    ->options(fn () => TraderStatement::where('trader_id', $this->record->id)
                        ->orderByDesc('opened_at')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => ($s->title ? $s->title.' | ' : '').$s->opened_at->format('Y-m-d').' | '.$s->status->getLabel()])
                        ->toArray())
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('journalEntry', fn ($q) => $q->where('trader_statement_id', $data['value']))
                        : $query),
                DateRangeFilter::make('date')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '>=', Carbon::parse($date))),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', Carbon::parse($date))),
                            );
                    }),
            ]);
    }

    public function setActiveStatementId(int $id): void
    {
        $this->activeStatementId = $id;
    }
}
