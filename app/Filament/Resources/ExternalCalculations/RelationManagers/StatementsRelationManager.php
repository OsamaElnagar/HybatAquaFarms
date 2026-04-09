<?php

namespace App\Filament\Resources\ExternalCalculations\RelationManagers;

use App\Enums\ExternalCalculationStatementStatus;
use App\Enums\ExternalCalculationType;
use App\Models\ExternalCalculationEntry;
use App\Models\ExternalCalculationStatement;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatementsRelationManager extends RelationManager
{
    protected static string $relationship = 'statements';

    protected static ?string $title = 'كشوف حسابات';

    protected static ?string $modelLabel = 'كشف حساب';

    protected static ?string $pluralModelLabel = 'كشوف حسابات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان الكشف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('تاريخ الفتح')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('تاريخ الإغلاق')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('لا تزال مفتوحة'),
                TextColumn::make('opening_balance')
                    ->label('رصيد افتتاحي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('total_credits')
                    ->label('إجمالي المقبوضات')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success'),
                TextColumn::make('total_debits')
                    ->label('إجمالي المدفوعات')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('danger'),
                TextColumn::make('net_balance')
                    ->label('الرصيد الختامي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ExternalCalculationStatementStatus::class),
            ])
            ->headerActions([
                // Usually handled by the main resource
            ])
            ->recordActions([
                Action::make('exportPdf')
                    ->label('تصدير PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (ExternalCalculationStatement $record) {
                        return $this->exportStatementPdf($record);
                    }),
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('opened_at', 'desc');
    }

    protected function exportStatementPdf(ExternalCalculationStatement $statement): StreamedResponse
    {
        $ownerRecord = $this->getOwnerRecord();

        $entries = ExternalCalculationEntry::query()
            ->where('external_calculation_statement_id', $statement->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date?->format('Y-m-d') ?? '-',
                'entry_number' => $entry->reference_number ?? '-',
                'description' => $entry->description ?? '-',
                'debit' => $entry->type === ExternalCalculationType::Payment ? (float) $entry->amount : 0,
                'credit' => $entry->type === ExternalCalculationType::Receipt ? (float) $entry->amount : 0,
            ])->toArray();

        $statementData = [
            'title' => $statement->title ?? 'كشف حساب',
            'status' => $statement->status?->value ?? 'open',
            'opened_at' => $statement->opened_at?->format('Y-m-d') ?? '-',
            'closed_at' => $statement->closed_at?->format('Y-m-d') ?? null,
            'opening_balance' => (float) $statement->opening_balance,
            'closing_balance' => (float) $statement->net_balance,
            'notes' => $statement->notes,
        ];

        $pdf = (new PdfService)->generateStatementPdf(
            'external_calculation',
            $ownerRecord->name,
            $statementData,
            $entries
        );

        $filename = 'كشف حساب لحساب خارجي - '.$ownerRecord->name.' - '.now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}
