<?php

namespace App\Filament\Exports;

use App\Models\PettyCashTransaction;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PettyCashTransactionExporter extends Exporter
{
    protected static ?string $model = PettyCashTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('pettyCash.name')->label('العهدة'),
            ExportColumn::make('farm.name')->label('المزرعة'),
            ExportColumn::make('batch.batch_code')->label('الدفعة'),
            ExportColumn::make('voucher.voucher_number')->label('الفاتورة'),
            ExportColumn::make('expenseCategory.name')->label('مصروف'),
            ExportColumn::make('employee.name')->label('الموظف'),
            ExportColumn::make('date')->label('التاريخ')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d')),
            ExportColumn::make('direction')->label('الاتجاه')->formatStateUsing(fn ($state) => $state->getLabel()),
            ExportColumn::make('amount')->label('المبلغ')->formatStateUsing(fn ($state) => Number::format($state)),
            ExportColumn::make('description')->label('الوصف'),
            ExportColumn::make('recordedBy.name')->label('مسجل بواسطة'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your petty cash transaction export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
