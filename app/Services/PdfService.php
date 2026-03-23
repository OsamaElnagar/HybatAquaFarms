<?php

namespace App\Services;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Mccarlosen\LaravelMpdf\LaravelMpdf;

class PdfService
{
    public function generateReportPdf(string $title, array $headers, $rows): LaravelMpdf
    {
        return PDF::loadView('pdf.generic-report', [
            'reportTitle' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'storeName' => config('app.name'),
            'date' => now()->format('Y-m-d h:i A'),
        ], [], [
            'title' => $title,
        ]);
    }

    public function generateDailyFarmReportPdf(array $data): LaravelMpdf
    {
        return PDF::loadView('pdf.daily-farm-report', [
            'data' => $data,
            'reportDate' => $data['date'] ?? now()->format('Y-m-d'),
        ], [], [
            'title' => 'التقرير اليومي للمزرعة - '.($data['date'] ?? now()->format('Y-m-d')),
        ]);
    }

    public function generateStatementPdf(
        string $type,
        string $entityName,
        array $statement,
        array $entries
    ): LaravelMpdf {
        return PDF::loadView('pdf.statement', [
            'type' => $type,
            'entityName' => $entityName,
            'statement' => $statement,
            'entries' => $entries,
            'generatedAt' => now()->format('Y-m-d h:i A'),
            'storeName' => config('app.name'),
        ], [], [
            'title' => "كشف حساب - {$entityName}",
        ]);
    }
}
