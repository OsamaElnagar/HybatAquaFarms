<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingUnit;
use App\Models\Farm;
use App\Models\HarvestBox;
use App\Models\SalesOrder;
use App\Models\Trader;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $traders = Trader::all();

        if ($traders->isEmpty()) {
            $this->command->warn("No traders found. Skipping sales orders seeding.");
            return;
        }

        // Get unsold harvest boxes
        $unsoldBoxes = HarvestBox::where('is_sold', false)->get();

        if ($unsoldBoxes->isEmpty()) {
            $this->command->warn("No unsold harvest boxes found. Run HarvestSeeder first.");
            return;
        }

        // Group boxes by farm for realistic orders
        $boxesByFarm = $unsoldBoxes->groupBy('batch.farm_id');

        foreach ($boxesByFarm as $farmId => $farmBoxes) {
            $farm = Farm::find($farmId);

            if (!$farm) continue;

            // Create 2-5 sales orders per farm
            $orderCount = rand(2, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $trader = $traders->random();
                $orderDate = fake()->dateTimeBetween('-2 months', 'now');

                // Select random boxes for this order (3-10 boxes)
                $boxesForOrder = $farmBoxes
                    ->where('is_sold', false)
                    ->random(min(rand(3, 10), $farmBoxes->where('is_sold', false)->count()));

                if ($boxesForOrder->isEmpty()) {
                    break; // No more boxes available
                }

                // Create the sales order
                $salesOrder = SalesOrder::create([
                    'farm_id' => $farmId,
                    'trader_id' => $trader->id,
                    'date' => $orderDate,
                    'boxes_subtotal' => 0, // Will be calculated
                    'commission_rate' => $trader->commission_rate ?? 2.0,
                    'commission_amount' => 0,
                    'transport_cost' => $trader->default_transport_cost_flat ?? fake()->randomFloat(2, 50, 200),
                    'tax_amount' => 0,
                    'discount_amount' => fake()->boolean(20) ? fake()->randomFloat(2, 50, 500) : 0,
                    'total_before_commission' => 0,
                    'net_amount' => 0,
                    'payment_status' => fake()->randomElement([
                        PaymentStatus::Paid,
                        PaymentStatus::Paid,
                        PaymentStatus::Paid, // 60% paid
                        PaymentStatus::Partial,
                        PaymentStatus::Pending,
                    ]),
                    'delivery_status' => fake()->randomElement([
                        DeliveryStatus::DELIVERED,
                        DeliveryStatus::DELIVERED,
                        DeliveryStatus::DELIVERED, // 60% delivered
                        DeliveryStatus::PENDING,
                    ]),
                    'delivery_date' => fake()->dateTimeBetween($orderDate, '+7 days'),
                    'delivery_address' => fake()->optional(0.7)->address(),
                    'notes' => fake()->optional(0.3)->sentence(),
                    'created_by' => 1,
                ]);

                // Assign boxes to this order with pricing
                $lineNumber = 1;

                foreach ($boxesForOrder as $box) {
                    // Price based on classification
                    $unitPrice = $this->getPriceForClassification($box->classification);

                    $box->update([
                        'trader_id' => $trader->id,
                        'sales_order_id' => $salesOrder->id,
                        'unit_price' => $unitPrice,
                        'pricing_unit' => PricingUnit::Kilogram->value,
                        'is_sold' => true,
                        'sold_at' => $orderDate,
                        'line_number' => $lineNumber++,
                    ]);
                }

                // Recalculate totals (will be done by model observer, but let's be explicit)
                $salesOrder->recalculateTotals();
            }
        }

        $this->command->info("✅ Sales orders seeded successfully!");
        $this->command->info("Created: ".SalesOrder::count()." orders");
        $this->command->info("Sold boxes: ".HarvestBox::where('is_sold', true)->count());
    }

    /**
     * Get realistic price per kg based on classification
     */
    private function getPriceForClassification(?string $classification): float
    {
        return match($classification) {
            'جامبو' => fake()->randomFloat(2, 60, 75),
            'بلطي' => fake()->randomFloat(2, 45, 60),
            'نمرة 1' => fake()->randomFloat(2, 50, 65),
            'نمرة 2' => fake()->randomFloat(2, 40, 55),
            'نمرة 3' => fake()->randomFloat(2, 35, 45),
            'نمرة 4' => fake()->randomFloat(2, 25, 35),
            'خرط' => fake()->randomFloat(2, 20, 30),
            default => fake()->randomFloat(2, 30, 50),
        };
    }
}
