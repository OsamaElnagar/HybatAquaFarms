<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BatchMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batches = Batch::all();
        $users = User::all();

        if ($batches->isEmpty() || $users->isEmpty()) {
            $this->command->warn('لا توجد دفعات أو مستخدمين. يرجى تشغيل BatchSeeder و UserSeeder أولاً.');

            return;
        }

        foreach ($batches as $batch) {
            $batchUnitId = $batch->units->first()?->id;
            $entryDate = $batch->entry_date ?? now()->subDays(rand(30, 180));
            $currentQuantity = $batch->initial_quantity;
            $daysSinceEntry = now()->diffInDays($entryDate);

            // 1. Create entry movement for the batch
            BatchMovement::create([
                'batch_id' => $batch->id,
                'movement_type' => MovementType::Entry,
                'to_farm_id' => $batch->farm_id,
                'to_unit_id' => $batchUnitId,
                'quantity' => $batch->initial_quantity,
                'weight' => $batch->initial_quantity * ($batch->initial_weight_avg ?? 0) / 1000, // Convert to kg
                'date' => $entryDate,
                'reason' => 'إدخال دفعة جديدة',
                'recorded_by' => $users->random()->id,
                'notes' => 'إدخال أولي للدفعة',
            ]);

            // 2. Create mortality movements (random deaths over time)
            $mortalityCount = rand(2, 5);
            $totalMortality = 0;

            for ($i = 0; $i < $mortalityCount && $currentQuantity > 0; $i++) {
                $mortalityDate = Carbon::parse($entryDate)->addDays(rand(5, $daysSinceEntry));
                $mortalityQuantity = min(
                    rand(10, (int) ($currentQuantity * 0.05)), // Max 5% of current quantity
                    $currentQuantity - 100 // Keep at least 100 fish
                );

                if ($mortalityQuantity <= 0) {
                    continue;
                }

                $totalMortality += $mortalityQuantity;
                $currentQuantity -= $mortalityQuantity;

                BatchMovement::create([
                    'batch_id' => $batch->id,
                    'movement_type' => MovementType::Mortality,
                    'from_farm_id' => $batch->farm_id,
                    'from_unit_id' => $batchUnitId,
                    'quantity' => $mortalityQuantity,
                    'weight' => $mortalityQuantity * ($batch->current_weight_avg ?? 0) / 1000,
                    'date' => $mortalityDate,
                    'reason' => ['مرض', 'نفوق طبيعي', 'نقص أكسجين', 'تلوث'][rand(0, 3)],
                    'recorded_by' => $users->random()->id,
                    'notes' => 'نفوق عادي',
                ]);
            }

            // 3. Create transfer movements (if batch is old enough and has enough quantity)
            if ($daysSinceEntry > 30 && $currentQuantity > 1000) {
                $transferCount = rand(0, 2);

                for ($i = 0; $i < $transferCount && $currentQuantity > 500; $i++) {
                    $otherFarms = Farm::where('id', '!=', $batch->farm_id)->get();
                    $otherUnits = FarmUnit::where('farm_id', '!=', $batch->farm_id)
                        ->where('status', 'active')
                        ->get();

                    if ($otherFarms->isEmpty() || $otherUnits->isEmpty()) {
                        continue;
                    }

                    $toFarm = $otherFarms->random();
                    $toUnit = $otherUnits->where('farm_id', $toFarm->id)->first();

                    if (! $toUnit) {
                        continue;
                    }

                    $transferDate = Carbon::parse($entryDate)->addDays(rand(30, $daysSinceEntry));
                    $transferQuantity = min(rand(500, 2000), (int) ($currentQuantity * 0.3));

                    if ($transferQuantity <= 0) {
                        continue;
                    }

                    $currentQuantity -= $transferQuantity;

                    BatchMovement::create([
                        'batch_id' => $batch->id,
                        'movement_type' => MovementType::Transfer,
                        'from_farm_id' => $batch->farm_id,
                        'from_unit_id' => $batchUnitId,
                        'to_farm_id' => $toFarm->id,
                        'to_unit_id' => $toUnit->id,
                        'quantity' => $transferQuantity,
                        'weight' => $transferQuantity * ($batch->current_weight_avg ?? 0) / 1000,
                        'date' => $transferDate,
                        'reason' => 'نقل بين المزارع',
                        'recorded_by' => $users->random()->id,
                        'notes' => "نقل من {$batch->farm->name} إلى {$toFarm->name}",
                    ]);
                }
            }

            // 4. Create harvest movements (if batch is old enough)
            if ($daysSinceEntry > 90 && $currentQuantity > 500) {
                $harvestCount = rand(0, 1);

                for ($i = 0; $i < $harvestCount && $currentQuantity > 0; $i++) {
                    $harvestDate = Carbon::parse($entryDate)->addDays(rand(90, $daysSinceEntry));
                    $harvestQuantity = min(rand(500, 3000), (int) ($currentQuantity * 0.4));

                    if ($harvestQuantity <= 0) {
                        continue;
                    }

                    $currentQuantity -= $harvestQuantity;

                    BatchMovement::create([
                        'batch_id' => $batch->id,
                        'movement_type' => MovementType::Harvest,
                        'from_farm_id' => $batch->farm_id,
                        'from_unit_id' => $batchUnitId,
                        'quantity' => $harvestQuantity,
                        'weight' => $harvestQuantity * ($batch->current_weight_avg ?? 0) / 1000,
                        'date' => $harvestDate,
                        'reason' => 'حصاد',
                        'recorded_by' => $users->random()->id,
                        'notes' => 'حصاد جزئي',
                    ]);
                }
            }

            // Update batch current quantity based on movements
            // Note: The observer already updated it, but we'll recalculate to ensure accuracy
            $batch->refresh();
            $recalculatedQuantity = $batch->initial_quantity;

            foreach ($batch->movements()->orderBy('date')->orderBy('id')->get() as $movement) {
                match ($movement->movement_type) {
                    MovementType::Entry => $recalculatedQuantity += $movement->quantity,
                    MovementType::Transfer, MovementType::Harvest, MovementType::Mortality => $recalculatedQuantity -= $movement->quantity,
                };
            }

            $batch->update(['current_quantity' => max(0, $recalculatedQuantity)]);
        }

        $this->command->info('تم إنشاء '.BatchMovement::count().' حركة زريعة بنجاح.');
    }
}
