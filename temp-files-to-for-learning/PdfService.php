<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class PdfService
{
    /**
     * Generate a sale receipt PDF (A4 format).
     */
    public function saleReceipt(Sale $sale): \Mccarlosen\LaravelMpdf\LaravelMpdf
    {
        $sale->loadMissing([
            'customer',
            'items.productVariant.product',
            'payments.receiver',
            'creator',
        ]);

        return PDF::loadView('pdf.sale-receipt', [
            'sale' => $sale,
            'storeName' => config('app.name'),
        ], [], [
            'title' => "Receipt #{$sale->sale_number}",
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);
    }

    /**
     * Generate a thermal-format sale receipt (58mm / 80mm width).
     */
    public function saleThermalReceipt(Sale $sale): \Mccarlosen\LaravelMpdf\LaravelMpdf
    {
        $sale->loadMissing([
            'customer',
            'items.productVariant.product',
            'payments',
            'creator',
        ]);

        return PDF::loadView('pdf.sale-thermal', [
            'sale' => $sale,
            'storeName' => config('app.name'),
        ], [], [
            'title' => "Receipt #{$sale->sale_number}",
            'format' => [80, 297],
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
            'default_font_size' => '9',
        ]);
    }

    /**
     * Generate a purchase order document PDF.
     */
    public function purchaseOrder(Purchase $purchase): \Mccarlosen\LaravelMpdf\LaravelMpdf
    {
        $purchase->loadMissing([
            'supplier',
            'items.productVariant.product',
            'payments',
            'creator',
        ]);

        return PDF::loadView('pdf.purchase-order', [
            'purchase' => $purchase,
            'storeName' => config('app.name'),
        ], [], [
            'title' => "Purchase Order #{$purchase->purchase_number}",
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);
    }

    /**
     * Generate a customer account statement PDF.
     *
     * @param  string|null  $fromDate  Y-m-d format
     * @param  string|null  $toDate  Y-m-d format
     */
    public function customerStatement(
        Customer $customer,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): \Mccarlosen\LaravelMpdf\LaravelMpdf {
        $salesQuery = $customer->sales()->with('payments')->latest('sale_date');

        if ($fromDate) {
            $salesQuery->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate) {
            $salesQuery->whereDate('sale_date', '<=', $toDate);
        }

        $sales = $salesQuery->get();

        return PDF::loadView('pdf.customer-statement', [
            'customer' => $customer,
            'sales' => $sales,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'storeName' => config('app.name'),
        ], [], [
            'title' => "Statement - {$customer->name}",
        ]);
    }

    /**
     * Generate a supplier account statement PDF.
     *
     * @param  string|null  $fromDate  Y-m-d format
     * @param  string|null  $toDate  Y-m-d format
     */
    public function supplierStatement(
        Supplier $supplier,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): \Mccarlosen\LaravelMpdf\LaravelMpdf {
        $purchasesQuery = $supplier->purchases()->with('payments')->latest('purchase_date');

        if ($fromDate) {
            $purchasesQuery->whereDate('purchase_date', '>=', $fromDate);
        }

        if ($toDate) {
            $purchasesQuery->whereDate('purchase_date', '<=', $toDate);
        }

        $purchases = $purchasesQuery->get();

        return PDF::loadView('pdf.supplier-statement', [
            'supplier' => $supplier,
            'purchases' => $purchases,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'storeName' => config('app.name'),
        ], [], [
            'title' => "Statement - {$supplier->name}",
        ]);
    }

    /**
     * Generate a generic report PDF.
     */
    public function generateReportPdf(string $title, array $headers, $rows): \Mccarlosen\LaravelMpdf\LaravelMpdf
    {
        return PDF::loadView('pdf.generic-report', [
            'reportTitle' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'storeName' => config('app.name'),
            'date' => now()->format('Y-m-d H:i'),
        ], [], [
            'title' => $title,
        ]);
    }
}
