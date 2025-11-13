<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\FactoryPayment;
use App\Models\FeedMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class FactoryPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get feed factories (not hatcheries)
        $feedFactories = Factory::whereNotIn('code', [
            'HAT-ABD', 'HAT-NAD', 'HAT-ALI', 'HAT-MOH', 'HAT-ELK',
        ])->get();

        if ($feedFactories->isEmpty()) {
            // Fallback to all factories if no feed factories found
            $feedFactories = Factory::all();
        }

        $users = User::all();
        $paymentMethods = ['cash', 'bank', 'check'];

        foreach ($feedFactories as $factory) {
            // Get feed movements for this factory to calculate total purchases
            $feedMovements = FeedMovement::where('factory_id', $factory->id)
                ->where('movement_type', 'in')
                ->get();

            if ($feedMovements->isEmpty()) {
                continue;
            }

            // Calculate total purchases (using standard_cost from FeedItem)
            $totalPurchases = $feedMovements->sum(function ($movement) {
                $feedItem = $movement->feedItem;
                $unitCost = $feedItem?->standard_cost ?? 0;

                return (float) $movement->quantity * $unitCost;
            });

            // Get existing payments
            $totalPaid = (float) $factory->factoryPayments()->sum('amount');
            $remaining = $totalPurchases - $totalPaid;

            // Only create payments if there's something to pay
            if ($remaining <= 0) {
                continue;
            }

            // Create 1-4 payments per factory (installment-based, like real-world)
            $paymentCount = rand(1, 4);
            $paymentAmount = $remaining / $paymentCount;

            for ($i = 0; $i < $paymentCount; $i++) {
                // Last payment gets any remainder
                $amount = ($i === $paymentCount - 1) ? $remaining : round($paymentAmount, 2);
                $remaining -= $amount;

                if ($amount <= 0) {
                    continue;
                }

                // Get a date from feed movements
                $latestMovement = $feedMovements->sortByDesc('date')->first();
                $paymentDate = $latestMovement?->date?->addDays(rand(15, 90)) ?? now()->subDays(rand(30, 180));

                FactoryPayment::create([
                    'factory_id' => $factory->id,
                    'date' => $paymentDate,
                    'amount' => $amount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference_number' => rand(0, 1) ? 'REF-'.rand(1000, 9999) : null,
                    'description' => "دفعة لمصنع {$factory->name}",
                    'recorded_by' => $users->random()->id,
                    'notes' => rand(0, 1) ? 'دفعة عبر '.['نقدي', 'تحويل بنكي', 'شيك'][rand(0, 2)] : null,
                ]);
            }
        }
    }
}
