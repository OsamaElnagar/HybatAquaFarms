<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\SalesOrder;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesItem>
 */
class SalesItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salesOrder = SalesOrder::inRandomOrder()->first();
        $batch = Batch::where("farm_id", $salesOrder->farm_id ?? null)
            ->inRandomOrder()
            ->first();
        $speciesId = $batch
            ? $batch->species_id
            : Species::inRandomOrder()->first()?->id;

        // Random size category with realistic weight ranges
        $sizeCategories = [
            "زريعة" => [
                "min_weight" => 0.005,
                "max_weight" => 0.01,
                "name" => "زريعة",
            ],
            "إصبعيات" => [
                "min_weight" => 0.01,
                "max_weight" => 0.05,
                "name" => "إصبعيات",
            ],
            "صغير" => [
                "min_weight" => 0.05,
                "max_weight" => 0.15,
                "name" => "صغير",
            ],
            "متوسط" => [
                "min_weight" => 0.15,
                "max_weight" => 0.3,
                "name" => "متوسط",
            ],
            "كبير" => [
                "min_weight" => 0.3,
                "max_weight" => 0.5,
                "name" => "كبير",
            ],
            "جامبو" => [
                "min_weight" => 0.5,
                "max_weight" => 1.0,
                "name" => "جامبو",
            ],
        ];

        $selectedSize = fake()->randomElement($sizeCategories);
        $avgFishWeightKg = fake()->randomFloat(
            3,
            $selectedSize["min_weight"],
            $selectedSize["max_weight"],
        );
        $avgFishWeightGrams = $avgFishWeightKg * 1000;

        // Generate quantity based on realistic ranges
        $quantity = fake()->numberBetween(50, 2000);
        $weightKg = $quantity * $avgFishWeightKg;

        // Grade with weighted distribution (more A and B grades)
        $gradeWeights = ["A" => 40, "B" => 35, "C" => 20, "D" => 5];
        $grade = fake()->randomElement(
            array_merge(
                ...array_map(
                    fn($grade, $weight) => array_fill(0, $weight, $grade),
                    array_keys($gradeWeights),
                    array_values($gradeWeights),
                ),
            ),
        );

        // Pricing based on size and grade
        $basePrice = match ($selectedSize["name"]) {
            "زريعة" => fake()->randomFloat(2, 0.5, 2.0),
            "إصبعيات" => fake()->randomFloat(2, 3.0, 8.0),
            "صغير" => fake()->randomFloat(2, 20.0, 30.0),
            "متوسط" => fake()->randomFloat(2, 30.0, 40.0),
            "كبير" => fake()->randomFloat(2, 40.0, 50.0),
            "جامبو" => fake()->randomFloat(2, 50.0, 70.0),
        };

        // Grade multiplier
        $gradeMultiplier = match ($grade) {
            "A" => 1.2,
            "B" => 1.0,
            "C" => 0.85,
            "D" => 0.7,
            default => 1.0,
        };

        $unitPrice = round($basePrice * $gradeMultiplier, 2);

        // Pricing unit (mostly kg)
        $pricingUnit = fake()->randomElement(["kg" => 80, "piece" => 20]);
        $pricingUnit = fake()->randomElement(
            array_merge(array_fill(0, 80, "kg"), array_fill(0, 20, "piece")),
        );

        // Calculate subtotal
        $subtotal =
            $pricingUnit === "piece"
                ? $quantity * $unitPrice
                : $weightKg * $unitPrice;

        // Discount (30% chance of discount)
        $hasDiscount = fake()->boolean(30);
        $discountPercent = $hasDiscount ? fake()->randomFloat(2, 2, 15) : 0;
        $discountAmount = $subtotal * ($discountPercent / 100);
        $totalPrice = $subtotal - $discountAmount;

        // Fulfillment status
        $fulfillmentStatuses = [
            "fulfilled" => 60,
            "partial" => 25,
            "pending" => 15,
        ];
        $fulfillmentStatus = fake()->randomElement(
            array_merge(
                array_fill(0, 60, "fulfilled"),
                array_fill(0, 25, "partial"),
                array_fill(0, 15, "pending"),
            ),
        );

        $fulfilledQuantity = match ($fulfillmentStatus) {
            "fulfilled" => $quantity,
            "partial" => fake()->numberBetween(
                $quantity * 0.3,
                $quantity * 0.8,
            ),
            "pending" => 0,
        };

        $fulfilledWeight = ($fulfilledQuantity / $quantity) * $weightKg;

        $species = Species::find($speciesId);
        $itemName =
            ($species?->name ?? "سمك") .
            " " .
            $selectedSize["name"] .
            " - درجة " .
            $grade;

        return [
            "sales_order_id" => $salesOrder?->id ?? SalesOrder::factory(),
            "batch_id" => $batch?->id,
            "species_id" => $speciesId ?? Species::factory(),
            "item_name" => $itemName,
            "description" => fake()->optional(0.3)->sentence(),
            "quantity" => $quantity,
            "weight_kg" => round($weightKg, 3),
            "average_fish_weight" => round($avgFishWeightGrams, 3),
            "grade" => $grade,
            "size_category" => $selectedSize["name"],
            "unit_price" => $unitPrice,
            "pricing_unit" => $pricingUnit,
            "discount_percent" => round($discountPercent, 2),
            "discount_amount" => round($discountAmount, 2),
            "subtotal" => round($subtotal, 2),
            "total_price" => round($totalPrice, 2),
            "fulfilled_quantity" => round($fulfilledQuantity, 2),
            "fulfilled_weight" => round($fulfilledWeight, 3),
            "fulfillment_status" => $fulfillmentStatus,
            "line_number" => fake()->numberBetween(1, 10),
            "notes" => fake()->optional(0.2)->sentence(),
        ];
    }
}
