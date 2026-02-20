<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
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
            'date' => now()->format('Y-m-d H:i A'),
        ], [], [
            'title' => $title,
        ]);
    }
}
