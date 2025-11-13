<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\BatchPayment;
use App\Models\Factory;
use App\Models\User;
use Illuminate\Database\Seeder;

class BatchPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get batches that have factory_id and total_cost
        $batches = Batch::whereNotNull('factory_id')
            ->whereNotNull('total_cost')
            ->where('total_cost', '>', 0)
            ->get();

        if ($batches->isEmpty()) {
            return;
        }

        // Get hatcheries (factories that supply batches)
        $hatcheries = Factory::whereIn('code', [
            'HAT-ABD', 'HAT-NAD', 'HAT-ALI', 'HAT-MOH', 'HAT-ELK',
        ])->get();

        if ($hatcheries->isEmpty()) {
            // Fallback to any factory if hatcheries don't exist
            $hatcheries = Factory::all();
        }

        $users = User::all();
        $paymentMethods = ['cash', 'bank', 'check'];

        foreach ($batches as $batch) {
            // Only create payments for batches that have a factory
            if (! $batch->factory_id) {
                continue;
            }

            $totalCost = (float) $batch->total_cost;
            $totalPaid = (float) $batch->batchPayments()->sum('amount');
            $remaining = $totalCost - $totalPaid;

            // Create 1-3 payments per batch (installment-based)
            $paymentCount = rand(1, 3);
            $paymentAmount = $remaining / $paymentCount;

            for ($i = 0; $i < $paymentCount; $i++) {
                // Last payment gets any remainder
                $amount = ($i === $paymentCount - 1) ? $remaining : round($paymentAmount, 2);
                $remaining -= $amount;

                if ($amount <= 0) {
                    continue;
                }

                BatchPayment::create([
                    'batch_id' => $batch->id,
                    'factory_id' => $batch->factory_id,
                    'date' => $batch->entry_date->addDays(rand(30, 180)),
                    'amount' => $amount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference_number' => rand(0, 1) ? 'REF-'.rand(1000, 9999) : null,
                    'description' => "دفعة جزئية لزريعة - دفعة {$batch->batch_code}",
                    'recorded_by' => $users->random()->id,
                    'notes' => rand(0, 1) ? 'دفعة عبر '.['نقدي', 'تحويل بنكي', 'شيك'][rand(0, 2)] : null,
                ]);
            }
        }
    }
}
