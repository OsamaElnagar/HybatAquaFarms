<?php

namespace App\Services;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class PdfService
{
    public function generateReportPdf(string $title, array $headers, $rows): \Mccarlosen\LaravelMpdf\LaravelMpdf
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

    public function generateDailyFarmReportPdf(array $data): \Mccarlosen\LaravelMpdf\LaravelMpdf
    {
        return PDF::loadView('pdf.daily-farm-report', [
            'data' => $data,
            'reportDate' => $data['date'] ?? now()->format('Y-m-d'),
        ], [], [
            'title' => 'التقرير اليومي للمزرعة - '.($data['date'] ?? now()->format('Y-m-d')),
        ]);
    }
}
