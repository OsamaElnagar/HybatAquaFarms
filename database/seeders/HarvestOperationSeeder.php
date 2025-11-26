<?php

namespace Database\Seeders;

use App\Enums\HarvestOperationStatus;
use App\Models\Batch;
use App\Models\HarvestOperation;
use Illuminate\Database\Seeder;

class HarvestOperationSeeder extends Seeder
{
    public function run(): void
    {
        // Get active or harvested batches
        $batches = Batch::whereIn('status', ['active', 'harvested'])
            ->has('unit')
            ->limit(10)
            ->get();

        foreach ($batches as $batch) {
            $startDate = fake()->dateTimeBetween('-3 months', '-1 month');
            $durationDays = rand(3, 21); // 3-21 days harvest operation
            $endDate = (clone $startDate)->modify("+{$durationDays} days");

            HarvestOperation::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => HarvestOperationStatus::Completed,
                'estimated_duration_days' => $durationDays,
                'notes' => fake()->optional(0.3)->sentence(),
                'created_by' => 1,
            ]);
        }

        echo 'تم إنشاء '.HarvestOperation::count()." عملية حصاد بنجاح.\n";
    }
}
