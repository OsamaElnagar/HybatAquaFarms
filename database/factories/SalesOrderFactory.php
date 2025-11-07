<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 50000);
        $tax = $subtotal * 0.14; // 14% tax
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $total = $subtotal + $tax - $discount;

        return [
            'order_number' => fake()->unique()->bothify('SO-####'),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'payment_status' => PaymentStatus::Pending,
        ];
    }
}
