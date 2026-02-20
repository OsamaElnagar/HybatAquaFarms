<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\SaleStatus;
use App\Enums\SaleType;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosService
{
    /**
     * Look up a product variant by barcode.
     *
     * Searches ProductVariant.barcode first, falls back to Product.barcode.
     */
    public function lookupByBarcode(string $identifier): ?ProductVariant
    {
        $identifier = trim($identifier);

        // Handle full URLs (e.g., from QR codes) - extract the last alphanumeric segment
        if (str_starts_with($identifier, 'http://') || str_starts_with($identifier, 'https://')) {
            $path = rtrim(parse_url($identifier, PHP_URL_PATH), '/');
            $identifier = basename($path);
        }

        // Try variant barcode or SKU first
        $variant = ProductVariant::query()
            ->where(function ($q) use ($identifier) {
                $q->where('barcode', $identifier)
                    ->orWhere('sku', $identifier);
            })
            ->where('is_active', true)
            ->with('product')
            ->first();

        if ($variant) {
            return $variant;
        }

        // Fall back to product barcode, SKU, or slug — return first active variant
        $product = Product::query()
            ->where(function ($q) use ($identifier) {
                $q->where('barcode', $identifier)
                    ->orWhere('sku', $identifier)
                    ->orWhere('slug', $identifier);
            })
            ->where('is_active', true)
            ->with(['variants' => fn ($q) => $q->where('is_active', true)])
            ->first();

        return $product?->variants->first();
    }

    /**
     * Check if enough stock is available for a variant.
     */
    public function validateStock(int $variantId, int $requestedQty): bool
    {
        $variant = ProductVariant::find($variantId);

        if (! $variant) {
            return false;
        }

        return $variant->current_stock >= $requestedQty;
    }

    /**
     * Complete a POS sale within a DB transaction.
     *
     * @param  array<int, array{variant_id: int, quantity: int, unit_price: float, cost_price: float, discount_amount: float, tax_amount: float}>  $cartItems
     * @param  array{customer_id: ?int, sale_type: SaleType, notes: ?string}  $saleData
     * @param  array{payment_method: PaymentMethod, amount_tendered: float}  $paymentData
     */
    public function completeSale(array $cartItems, array $saleData, array $paymentData): Sale
    {
        return DB::transaction(function () use ($cartItems, $saleData, $paymentData) {
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            // Pre-calculate totals
            foreach ($cartItems as $item) {
                $lineSubtotal = ($item['unit_price'] * $item['quantity']) - $item['discount_amount'];
                $subtotal += $lineSubtotal;
                $totalTax += $item['tax_amount'];
                $totalDiscount += $item['discount_amount'];
            }

            $totalAmount = $subtotal + $totalTax;
            $paidAmount = min($paymentData['amount_tendered'], $totalAmount);

            // Determine payment status
            $paymentStatus = match (true) {
                $paidAmount >= $totalAmount => PaymentStatus::Paid,
                $paidAmount > 0 => PaymentStatus::Partial,
                default => PaymentStatus::Pending,
            };

            $saleStatus = $saleData['status'] ?? SaleStatus::Completed;

            // Create Sale
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'customer_id' => $saleData['customer_id'] ?? null,
                'sale_date' => now(),
                'sale_type' => $saleData['sale_type'] ?? SaleType::Retail,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'discount_percentage' => 0,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'status' => $saleStatus,
                'notes' => $saleData['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create SaleItems & handle stock
            foreach ($cartItems as $item) {
                $lineSubtotal = ($item['unit_price'] * $item['quantity']) - $item['discount_amount'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'discount_amount' => $item['discount_amount'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $lineSubtotal,
                ]);

                // Only deduct stock for completed sales (not held/pending)
                if ($saleStatus === SaleStatus::Completed) {
                    $this->deductStock($item['variant_id'], $item['quantity'], $sale, $item['cost_price']);
                }
            }

            // Create Payment record if amount > 0
            if ($paidAmount > 0 && $saleStatus === SaleStatus::Completed) {
                Payment::create([
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'payment_type' => PaymentType::Incoming,
                    'amount' => $paidAmount,
                    'payment_method' => $paymentData['payment_method'] ?? PaymentMethod::Cash,
                    'payment_date' => now(),
                    'received_by' => Auth::id(),
                ]);
            }

            return $sale->fresh(['items.productVariant.product', 'payments']);
        });
    }

    /**
     * Deduct stock for a sold variant and create a stock movement record.
     */
    private function deductStock(int $variantId, int $quantity, Sale $sale, float $unitCost): void
    {
        $variant = ProductVariant::findOrFail($variantId);
        $previousStock = $variant->current_stock;

        $variant->decrement('current_stock', $quantity);

        StockMovement::create([
            'product_variant_id' => $variantId,
            'movement_type' => MovementType::Sale,
            'quantity' => -$quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $previousStock - $quantity,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'unit_cost' => $unitCost,
            'notes' => "POS Sale #{$sale->sale_number}",
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Generate an auto-incrementing sale number for the current year.
     */
    private function generateSaleNumber(): string
    {
        $year = date('Y');
        $lastSale = Sale::query()
            ->where('sale_number', 'like', "SAL-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastSale && preg_match('/(\d+)$/', $lastSale->sale_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return 'SAL-'.$year.'-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
