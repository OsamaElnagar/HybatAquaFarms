<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function __construct(
        protected PdfService $pdfService,
    ) {}

    public function saleReceipt(Sale $sale): Response
    {
        $content = $this->pdfService->saleReceipt($sale)->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"RECIEPT-{$sale->sale_number}.pdf\"",
        ]);
    }

    public function saleThermal(Sale $sale): Response
    {
        $content = $this->pdfService->saleThermalReceipt($sale)->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"THERMAL-{$sale->sale_number}.pdf\"",
        ]);
    }

    public function purchaseOrder(Purchase $purchase): Response
    {
        $content = $this->pdfService->purchaseOrder($purchase)->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"PO-{$purchase->purchase_number}.pdf\"",
        ]);
    }

    public function customerStatement(Request $request, Customer $customer): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $content = $this->pdfService->customerStatement(
            $customer,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        )->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"STATEMENT-{$customer->name}.pdf\"",
        ]);
    }

    public function supplierStatement(Request $request, Supplier $supplier): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $content = $this->pdfService->supplierStatement(
            $supplier,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        )->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"STATEMENT-{$supplier->name}.pdf\"",
        ]);
    }
}
