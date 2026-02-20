<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('sales/{sale}/receipt', [PdfController::class, 'saleReceipt'])->name('sale.receipt');
    Route::get('sales/{sale}/thermal', [PdfController::class, 'saleThermal'])->name('sale.thermal');
    Route::get('purchases/{purchase}/order', [PdfController::class, 'purchaseOrder'])->name('purchase.order');
    Route::get('customers/{customer}/statement', [PdfController::class, 'customerStatement'])->name('customer.statement');
    Route::get('suppliers/{supplier}/statement', [PdfController::class, 'supplierStatement'])->name('supplier.statement');
});
