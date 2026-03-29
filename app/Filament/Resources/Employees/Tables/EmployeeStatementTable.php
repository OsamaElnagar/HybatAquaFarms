<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\AdvanceRepayment;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeStatement;
use App\Models\JournalLine;
use App\Models\SalaryRecord;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeStatementTable
{
    public static function configure(Table $table, $livewire): Table
    {
        return $table
            ->query(
                JournalLine::query()
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
                    ->where(function (Builder $q) use ($livewire) {
                        $q->when(
                            $livewire->activeStatementId,
                            fn (Builder $q) => $q->whereHas('journalEntry', fn ($inner) => $inner->where('employee_statement_id', $livewire->activeStatementId)),
                            fn (Builder $q) => $q->whereHas('journalEntry', fn ($inner) => $inner->whereHas('employeeStatement', fn ($s) => $s->where('employee_id', $livewire->record->id)))
                                // Also include entries that might not be linked to a statement but belong to this employee's source records
                                ->orWhereHas('journalEntry', fn ($inner) => $inner->where(function ($sourceQ) use ($livewire) {
                                    $sourceQ->where(fn ($sq) => $sq->where('source_type', EmployeeAdvance::class)->whereIn('source_id', $livewire->record->advances->pluck('id')))
                                        ->orWhere(fn ($sq) => $sq->where('source_type', AdvanceRepayment::class)->whereIn('source_id', $livewire->record->advanceRepayments->pluck('id')))
                                        ->orWhere(fn ($sq) => $sq->where('source_type', SalaryRecord::class)->where('employee_id', $livewire->record->id));
                                }))
                        );
                    })
                    ->with(['journalEntry', 'account'])
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
                    ->label('مدين (عليه/سُلفة)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('danger')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي السُلف')),
                TextColumn::make('credit')
                    ->label('دائن (له/سداد)')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->summarize(Sum::make()->money('EGP', locale: 'en', decimalPlaces: 0)->label('إجمالي السداد')),
            ])
            ->defaultSort('journalEntry.date', 'desc')
            ->filters([
                SelectFilter::make('statement')
                    ->label('الكشف / الجلسة')
                    ->options(fn () => EmployeeStatement::where('employee_id', $livewire->record->id)
                        ->orderByDesc('opened_at')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => ($s->title ? $s->title.' | ' : '').$s->opened_at->format('Y-m-d').' | '.$s->status->getLabel()])
                        ->toArray())
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('journalEntry', fn ($q) => $q->where('employee_statement_id', $data['value']))
                        : $query),
            ]);
    }
}
