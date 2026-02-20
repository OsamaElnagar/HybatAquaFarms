<?php

namespace App\Filament\Pages;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\SaleType;
use App\Filament\Resources\Sales\Schemas\POSForm;
use App\Models\ProductVariant;
use App\Services\PosService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class PointOfSale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \UnitEnum|string|null $navigationGroup = 'Sales';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Point of Sale';

    protected static ?string $title = 'Point of Sale';

    protected string $view = 'filament.pages.point-of-sale';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'items' => [],
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'amount_tendered' => 0,
            'sale_type' => SaleType::Retail->value,
            'payment_method' => PaymentMethod::Cash->value,
        ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('checkout')
                ->label('Complete Sale')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(fn ($livewire) => $livewire->create(SaleStatus::Completed)),

            Action::make('hold')
                ->label('Hold Sale (Save For later)')
                ->icon('heroicon-o-pause')
                ->color(Color::Blue)
                ->action(fn ($livewire) => $livewire->create(SaleStatus::Pending)),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->action(fn ($livewire) => $livewire->resetForm())
                ->requiresConfirmation(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return POSForm::configure($schema)
            ->statePath('data');
    }

    public function addItemByBarcode(string $barcode, Get $get, Set $set): void
    {
        $service = app(PosService::class);
        $variant = $service->lookupByBarcode($barcode);

        if (! $variant) {
            Notification::make()->title('Product not found')->danger()->send();

            return;
        }

        $this->addVariantToRepeater($variant, $get, $set);
    }

    public function addItemById(int $id, Get $get, Set $set): void
    {
        $variant = ProductVariant::with('product')->find($id);

        if (! $variant) {
            return; // Should not happen via select
        }

        $this->addVariantToRepeater($variant, $get, $set);
    }

    protected function addVariantToRepeater(ProductVariant $variant, Get $get, Set $set): void
    {
        $items = $get('items') ?? [];

        // Check availability
        if ($variant->current_stock < 1) {
            Notification::make()->title("{$variant->product->name} is out of stock")->danger()->send();

            return;
        }

        // Check if exists
        foreach ($items as $key => $item) {
            if ($item['variant_id'] == $variant->id) {
                // Increment
                $newQty = $item['quantity'] + 1;
                if ($newQty > $variant->current_stock) {
                    Notification::make()->title("Insufficient stock for {$variant->product->name}")->warning()->send();

                    return;
                }

                // Update quantity directly in the state array
                $items[$key]['quantity'] = $newQty;
                // Recalculate that row's subtotal and tax manually since we are manipulating array directly
                $items[$key]['subtotal'] = round($newQty * $items[$key]['unit_price'], 2);
                $items[$key]['tax_amount'] = round(($items[$key]['subtotal'] - $items[$key]['discount_amount']) * ($items[$key]['tax_rate'] / 100), 2);

                $set('items', $items);
                $this->calculateTotals($get, $set);

                return;
            }
        }

        // Add new
        $unitPrice = $variant->selling_price; // Or wholesale logic
        // Simple tax calculation
        $taxRate = $variant->product->tax_rate ?? 0;

        $items[] = [
            'variant_id' => $variant->id,
            'product_name' => $variant->product->name.($variant->variant_name ? " - {$variant->variant_name}" : ''),
            'unit_price' => $unitPrice,
            'quantity' => 1,
            'subtotal' => $unitPrice,
            'cost_price' => $variant->cost_price,
            'tax_rate' => $taxRate,
            'tax_amount' => round($unitPrice * ($taxRate / 100), 2),
            'discount_amount' => 0,
            'stock' => $variant->current_stock,
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
        ];

        $set('items', $items);
        $this->calculateTotals($get, $set);

        Notification::make()
            ->title("{$variant->product->name} added to cart")
            ->success()
            ->send();
    }

    public function calculateRowTotal(Get $get, Set $set): void
    {
        // This runs inside the repeater row context
        $qty = (float) $get('quantity');
        $price = (float) $get('unit_price');
        $stock = (float) $get('stock');
        $taxRate = (float) $get('tax_rate');
        $discount = (float) $get('discount_amount');

        if ($qty > $stock) {
            Notification::make()->title('Insufficient stock')->warning()->send();
            $set('quantity', $stock);
            $qty = $stock;
        }

        $subtotal = round($qty * $price, 2);
        $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);

        $set('subtotal', $subtotal);
        $set('tax_amount', $taxAmount);
    }

    public function calculateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];

        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($items as $item) {
            $subtotal += (float) ($item['subtotal'] ?? 0);
            $tax += (float) ($item['tax_amount'] ?? 0);
            $discount += (float) ($item['discount_amount'] ?? 0);
        }

        $set('subtotal', round($subtotal, 2));
        $set('tax_amount', round($tax, 2));
        $set('discount_amount', round($discount, 2));
        $set('total_amount', round($subtotal + $tax - $discount, 2));
    }

    public function create(SaleStatus $status): void
    {
        $data = $this->form->getState();
        $items = $data['items'] ?? [];

        if (empty($items)) {
            Notification::make()->title('Cart is empty')->warning()->send();

            return;
        }

        $service = app(PosService::class);

        try {
            $sale = $service->completeSale(
                cartItems: $items, // Encoded as array from Repeater
                saleData: [
                    'customer_id' => $data['customer_id'],
                    'sale_type' => $data['sale_type'],
                    'notes' => $data['notes'],
                    'status' => $status,
                ],
                paymentData: [
                    'payment_method' => $data['payment_method'],
                    'amount_tendered' => (float) $data['amount_tendered'],
                ],
            );

            Notification::make()
                ->title($status === SaleStatus::Completed ? 'Sale Completed' : 'Sale Held')
                ->body("Sale #{$sale->sale_number} saved.")
                ->success()
                ->send();

            // High-value transaction alert
            $admin = \App\Models\User::first();
            if ($admin) {
                if ((float) $sale->total_amount >= 20000) {
                    $admin->notify(new \App\Notifications\HighValueSaleNotification($sale));
                }

                foreach ($sale->items as $item) {
                    $variant = $item->productVariant;
                    if ($variant) {
                        if ($variant->current_stock <= 0) {
                            $admin->notify(new \App\Notifications\OutStockCriticalNotification($variant));
                        } elseif ($variant->isLowStock()) {
                            $admin->notify(new \App\Notifications\LowStockNotification($variant));
                        }
                    }
                }
            }

            $this->resetForm();

        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function resetForm(): void
    {
        $this->form->fill([
            'items' => [],
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'amount_tendered' => 0,
            'sale_type' => SaleType::Retail,
            'payment_method' => PaymentMethod::Cash,
            'customer_id' => null,
            'notes' => null,
        ]);
    }
}
