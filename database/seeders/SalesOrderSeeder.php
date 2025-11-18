<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\SalesItem;
use App\Models\SalesOrder;
use App\Models\Trader;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $traders = Trader::all();

        if ($traders->isEmpty()) {
            $this->command->warn(
                "No traders found. Skipping sales orders seeding.",
            );
            return;
        }

        Farm::each(function ($farm) use ($traders) {
            $batches = Batch::where("farm_id", $farm->id)->get();

            if ($batches->isEmpty()) {
                $this->command->warn(
                    "No batches found for farm {$farm->name}. Skipping.",
                );
                return;
            }

            // Create 3-7 sales orders per farm
            $orderCount = rand(3, 7);

            for ($i = 0; $i < $orderCount; $i++) {
                $orderDate = now()->subDays(rand(1, 90));
                $itemsCount = rand(1, 4); // 1-4 items per order

                $salesOrder = SalesOrder::create([
                    "order_number" =>
                    "{$farm->code}-SO-" .
                        str_pad($i + 1, 4, "0", STR_PAD_LEFT),
                    "farm_id" => $farm->id,
                    "trader_id" => $traders->random()->id,
                    "date" => $orderDate,
                    "subtotal" => 0, // Will be calculated from items
                    "tax_amount" => 0,
                    "discount_amount" => 0, // Will be calculated from items
                    "total_amount" => 0, // Will be calculated from items
                    "payment_status" => fake()->randomElement([
                        PaymentStatus::Paid,
                        PaymentStatus::Paid,
                        PaymentStatus::Paid, // 60% paid
                        // PaymentStatus::Partial,
                        // PaymentStatus::Pending,
                    ]),
                    "delivery_status" => fake()->randomElement([
                        DeliveryStatus::DELIVERED,
                        DeliveryStatus::DELIVERED,
                        DeliveryStatus::DELIVERED, // 60% delivered
                        // DeliveryStatus::PENDING,
                        // DeliveryStatus::CANCELLED,
                    ]),
                    "delivery_date" => $orderDate->copy()->addDays(rand(1, 7)),
                    "delivery_address" => fake()->optional(0.7)->address(),
                    "notes" => fake()->optional(0.3)->sentence(),
                ]);

                // Create sales items for this order
                $orderSubtotal = 0;
                $orderTotalDiscount = 0;

                for (
                    $lineNumber = 1;
                    $lineNumber <= $itemsCount;
                    $lineNumber++
                ) {
                    $batch = $batches->random();

                    // Size categories with weights
                    $sizeCategories = [
                        "جامبو" => [
                            "min" => 0.5,
                            "max" => 1.0,
                            "price" => rand(50, 70),
                        ],
                        "كبير" => [
                            "min" => 0.3,
                            "max" => 0.5,
                            "price" => rand(40, 50),
                        ],
                        "متوسط" => [
                            "min" => 0.15,
                            "max" => 0.3,
                            "price" => rand(30, 40),
                        ],
                        "صغير" => [
                            "min" => 0.05,
                            "max" => 0.15,
                            "price" => rand(20, 30),
                        ],
                    ];

                    $sizeCategory = fake()->randomElement(
                        array_keys($sizeCategories),
                    );
                    $sizeData = $sizeCategories[$sizeCategory];

                    // Grade with pricing multiplier
                    $grades = [
                        "A" => 1.2,
                        "B" => 1.0,
                        "C" => 0.85,
                        "D" => 0.7,
                    ];
                    $grade = fake()->randomElement(array_keys($grades));
                    $gradeMultiplier = $grades[$grade];

                    // Calculate quantities and weights
                    $quantity = rand(100, 1500);
                    $avgFishWeightKg = fake()->randomFloat(
                        3,
                        $sizeData["min"],
                        $sizeData["max"],
                    );
                    $avgFishWeightGrams = $avgFishWeightKg * 1000;
                    $weightKg = $quantity * $avgFishWeightKg;

                    // Pricing
                    $pricingUnit = fake()->randomElement([
                        "kg",
                        "kg",
                        "kg",
                        "piece",
                    ]); // 75% kg
                    $baseUnitPrice = $sizeData["price"];
                    $unitPrice = round($baseUnitPrice * $gradeMultiplier, 2);

                    $subtotal =
                        $pricingUnit === "piece"
                        ? $quantity * $unitPrice
                        : $weightKg * $unitPrice;

                    // Discount (30% chance)
                    $hasDiscount = fake()->boolean(30);
                    $discountPercent = $hasDiscount
                        ? fake()->randomFloat(2, 2, 15)
                        : 0;
                    $discountAmount = $subtotal * ($discountPercent / 100);
                    $totalPrice = $subtotal - $discountAmount;

                    // Fulfillment status
                    $fulfillmentStatus = fake()->randomElement([
                        "fulfilled",
                        "fulfilled",
                        "fulfilled",
                        // "partial",
                        // "pending",
                    ]);
                    $fulfilledQuantity = match ($fulfillmentStatus) {
                        "fulfilled" => $quantity,
                        // "partial" => rand($quantity * 0.3, $quantity * 0.8),
                        // "pending" => 0,
                    };
                    $fulfilledWeight =
                        ($fulfilledQuantity / $quantity) * $weightKg;

                    $itemName =
                        $batch->species->name .
                        " " .
                        $sizeCategory .
                        " - درجة " .
                        $grade;

                    SalesItem::create([
                        "sales_order_id" => $salesOrder->id,
                        "batch_id" => $batch->id,
                        "species_id" => $batch->species_id,
                        "item_name" => $itemName,
                        "description" => fake()->optional(0.3)->sentence(),
                        "quantity" => $quantity,
                        "weight_kg" => round($weightKg, 3),
                        "average_fish_weight" => round($avgFishWeightGrams, 3),
                        "grade" => $grade,
                        "size_category" => $sizeCategory,
                        "unit_price" => $unitPrice,
                        "pricing_unit" => $pricingUnit,
                        "discount_percent" => round($discountPercent, 2),
                        "discount_amount" => round($discountAmount, 2),
                        "subtotal" => round($subtotal, 2),
                        "total_price" => round($totalPrice, 2),
                        "fulfilled_quantity" => round($fulfilledQuantity, 2),
                        "fulfilled_weight" => round($fulfilledWeight, 3),
                        "fulfillment_status" => $fulfillmentStatus,
                        "line_number" => $lineNumber,
                        "notes" => fake()->optional(0.2)->sentence(),
                    ]);

                    $orderSubtotal += $subtotal;
                    $orderTotalDiscount += $discountAmount;
                }

                // Update sales order with calculated totals
                $salesOrder->update([
                    "subtotal" => round($orderSubtotal, 2),
                    "discount_amount" => round($orderTotalDiscount, 2),
                    "total_amount" => round(
                        $orderSubtotal - $orderTotalDiscount,
                        2,
                    ),
                ]);
            }
        });

        $this->command->info("Sales orders and items seeded successfully!");
    }
}
