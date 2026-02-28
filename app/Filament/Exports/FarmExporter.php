<?php

namespace App\Filament\Exports;

use App\Models\Farm;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class FarmExporter extends Exporter
{
    protected static ?string $model = Farm::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('المعرف'),
            ExportColumn::make('code')
                ->label('الكود'),
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('size')
                ->label('المساحة'),
            ExportColumn::make('location')
                ->label('الموقع'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('established_date')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('manager.name')
                ->label('المدير'),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your farm export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
