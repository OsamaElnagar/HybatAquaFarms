<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\EmployeeAdvances\Actions\SettleWithExpensesAction;
use App\Filament\Resources\Employees\Actions\GiveAdvanceAction;
use App\Filament\Resources\Employees\Actions\MarkDaysOffAction;
use App\Filament\Resources\Employees\Actions\OpenNewEmployeeStatementAction;
use App\Filament\Resources\Employees\Actions\RepayAdvanceAction;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Tables\EmployeeStatementTable;
use App\Filament\Resources\Employees\Widgets\EmployeeStatementStats;
use App\Models\AdvanceRepayment;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeStatement;
use App\Models\FarmExpense;
use App\Models\JournalLine;
use App\Models\SalaryRecord;
use App\Services\PdfService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Size;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatementOfAccount extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = EmployeeResource::class;

    protected string $view = 'filament.resources.employees.pages.statement-of-account';

    protected static ?string $title = 'كشف حساب الموظف';

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
            $this->activeStatementId = $this->record->active_statement?->id;
        }
    }

    public function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
            ActionGroup::make([
                Action::make('exportPdf')
                    ->label('تصدير PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function () {
                        $this->exportStatementPdf();
                    }),

                OpenNewEmployeeStatementAction::make(),

                Action::make('statementsHistory')
                    ->label('سجل الكشوفات')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->url(fn () => EmployeeResource::getUrl('statements', ['record' => $this->record])),

                Action::make('viewAllHistory')
                    ->label($this->activeStatementId ? 'عرض كل التاريخ' : 'عرض الكشف الحالي فقط')
                    ->icon($this->activeStatementId ? 'heroicon-o-clock' : 'heroicon-o-document-text')
                    ->color('gray')
                    ->action(function () {
                        $this->activeStatementId = $this->activeStatementId
                            ? null
                            : $this->record->active_statement?->id;
                    }),

                GiveAdvanceAction::make(),
                RepayAdvanceAction::make(),
                SettleWithExpensesAction::make(),
                MarkDaysOffAction::make(),
            ])->label('الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size(Size::Small)
                ->color('primary')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        return EmployeeStatementTable::configure($table, $this);
    }

    public function setActiveStatementId(int $id): void
    {
        $this->activeStatementId = $id;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeStatementStats::class,
        ];
    }

    protected function exportStatementPdf(): void
    {
        $statement = $this->activeStatementId
            ? EmployeeStatement::find($this->activeStatementId)
            : null;

        $query = JournalLine::query()
            ->whereHas('account', fn (Builder $query) => $query->whereIn('code', ['1150', '5210']))
            ->where(function (Builder $query) {
                // Only show 5210 if 1150 is not present in the same entry to avoid duplication in repayments
                $query->whereHas('account', fn ($q) => $q->where('code', '1150'))
                    ->orWhere(function ($q) {
                        $q->whereHas('account', fn ($inner) => $inner->where('code', '5210'))
                            ->whereHas('journalEntry', function ($je) {
                                $je->whereDoesntHave('lines', function ($lines) {
                                    $lines->whereHas('account', fn ($acc) => $acc->where('code', '1150'));
                                });
                            });
                    });
            })
            ->where(function (Builder $q) {
                $q->when(
                    $this->activeStatementId,
                    fn (Builder $q) => $q->whereHas('journalEntry', fn ($inner) => $inner->where('employee_statement_id', $this->activeStatementId)),
                    fn (Builder $q) => $q->whereHas('journalEntry', fn ($inner) => $inner->whereHas('employeeStatement', fn ($s) => $s->where('employee_id', $this->record->id)))
                        ->orWhereHas('journalEntry', fn ($inner) => $inner->where(function ($sourceQ) {
                            $sourceQ->where(fn ($sq) => $sq->where('source_type', EmployeeAdvance::class)->whereIn('source_id', $this->record->advances->pluck('id')))
                                ->orWhere(fn ($sq) => $sq->where('source_type', AdvanceRepayment::class)->whereIn('source_id', $this->record->advanceRepayments->pluck('id')))
                                ->orWhere(fn ($sq) => $sq->where('source_type', FarmExpense::class)->whereIn('source_id', FarmExpense::whereIn('advance_repayment_id', $this->record->advanceRepayments->pluck('id'))->pluck('id')))
                                ->orWhere(fn ($sq) => $sq->where('source_type', SalaryRecord::class)->where('employee_id', $this->record->id));
                        }))
                );
            })
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->select('journal_lines.*', 'journal_entries.date as je_date', 'journal_entries.entry_number', 'journal_entries.description as je_description');

        $journalLines = $query->get();

        $entries = $journalLines->map(fn ($line) => [
            'date' => $line->je_date ? Carbon::parse($line->je_date)->format('Y-m-d') : '-',
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
            'closing_balance' => (float) ($statement?->net_balance ?? $this->record->total_outstanding_advances),
            'notes' => $statement?->notes,
        ];

        $pdf = (new PdfService)->generateStatementPdf(
            'employee',
            $this->record->name,
            $statementData,
            $entries
        );

        $filename = 'كشف حساب موظف - '.$this->record->name.' - '.now()->format('Y-m-d').'.pdf';

        $pdf->stream($filename);
    }
}
