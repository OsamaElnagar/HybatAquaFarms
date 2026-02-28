<?php

namespace App\Filament\Exports;

use App\Models\DailyFeedIssue;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class DailyFeedIssueExporter extends Exporter
{
    protected static ?string $model = DailyFeedIssue::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('farm.name')
                ->label('المزرعة'),
            ExportColumn::make('batch.batch_code')
                ->label('رقم الدفعة/الدورة'),
            ExportColumn::make('quantity')
                ->label('الكمية')->formatStateUsing(fn($state) => Number::format($state)),
            ExportColumn::make('feedItem.name')
                ->label('صنف العلف'),
            ExportColumn::make('warehouse.name')
                ->label('المستودع'),
            ExportColumn::make('date')
                ->label('التاريخ')->formatStateUsing(fn($state) => Carbon::parse($state)->format('Y-m-d')),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
            ExportColumn::make('recordedBy.name')
                ->label('مسجل بواسطة'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' فشل تصديرها.';
        }

        return $body;
    }
}
