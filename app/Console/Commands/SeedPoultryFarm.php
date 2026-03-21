<?php

namespace App\Console\Commands;

use App\Enums\BatchCycleType;
use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\MortalityRecord;
use App\Models\ProductionRecord;
use App\Models\Species;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedPoultryFarm extends Command
{
    protected $signature = 'seed:poultry-farm
                            {farmId? : Specific farm ID to seed}
                            {--days=30 : Number of days of historical production data}
                            {--birds=1000 : Number of birds in the batch}
                            {--mortality-rate=2 : Expected mortality rate percentage over the period}
                            {--egg-rate=85 : Expected daily egg production rate percentage}';

    protected $description = 'Seed a poultry farm with complete egg production workflow data';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $birdCount = (int) $this->option('birds');
        $mortalityRate = (float) $this->option('mortality-rate');
        $eggRate = (float) $this->option('egg-rate');

        $this->info('🐔 Starting Poultry Farm Seeder...');
        $this->info("   Days: {$days}, Birds: {$birdCount}, Mortality Rate: {$mortalityRate}%, Egg Rate: {$eggRate}%");
        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Get or create farm
            $farm = $this->getOrCreateFarm();
            $this->info("✓ Farm: {$farm->name} (ID: {$farm->id})");

            // Step 2: Create poultry species if needed
            $species = $this->getOrCreatePoultrySpecies();
            $this->info("✓ Species: {$species->name} (ID: {$species->id})");

            // Step 3: Create chicken coop farm units
            $units = $this->createChickenCoops($farm);
            $this->info('✓ Created '.count($units).' chicken coops');

            // Step 4: Create poultry batch
            $batch = $this->createPoultryBatch($farm, $species, $units, $birdCount, $days);
            $this->info("✓ Batch: {$batch->batch_code} (ID: {$batch->id})");
            $this->info("   Birds: {$batch->initial_quantity}, Current: {$batch->current_quantity}");

            // Step 5: Generate daily egg production records
            $productionCount = $this->generateProductionRecords($batch, $farm, $units, $days, $eggRate, $mortalityRate);
            $this->info("✓ Generated {$productionCount} egg production records");

            // Step 6: Generate mortality records
            $mortalityCount = $this->generateMortalityRecords($batch, $farm, $units, $days, $mortalityRate);
            $this->info("✓ Generated {$mortalityCount} mortality records");

            // Step 7: Verify batch quantities
            $this->verifyBatchQuantities($batch);

            DB::commit();

            $this->newLine();
            $this->info('✅ Poultry farm seeded successfully!');
            $this->info("   Run: php artisan serve --domain={$farm->code}.test");
            $this->info("   Then navigate to: المزارع → {$farm->name} → الدفعة {$batch->batch_code}");

            return static::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Seeding failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return static::FAILURE;
        }
    }

    private function getOrCreateFarm(): Farm
    {
        $farmId = $this->argument('farmId');

        if ($farmId) {
            $farm = Farm::find($farmId);
            if (! $farm) {
                throw new \RuntimeException("Farm with ID {$farmId} not found");
            }

            return $farm;
        }

        // Check for existing poultry farm
        $existingPoultryFarm = Farm::whereHas('batches', function ($q) {
            $q->where('cycle_type', BatchCycleType::Poultry);
        })->first();

        if ($existingPoultryFarm) {
            if ($this->confirm("Found existing poultry farm '{$existingPoultryFarm->name}'. Use it?", true)) {
                return $existingPoultryFarm;
            }
        }

        // Create new poultry farm
        $farmName = $this->ask('Enter poultry farm name', 'مزرعة الدواجن النموذجية');

        $farm = Farm::create([
            'code' => 'POULTRY-'.strtoupper(substr(md5(time()), 0, 4)),
            'name' => $farmName,
            'status' => 'active',
            'location' => 'Egypt',
        ]);

        return $farm;
    }

    private function getOrCreatePoultrySpecies(): Species
    {
        $species = Species::where('type', 'poultry')->first();

        if ($species) {
            return $species;
        }

        $speciesName = $this->ask('Enter poultry species name', 'دجاج بياض');

        return Species::create([
            'name' => $speciesName,
            'type' => 'poultry',
            'description' => 'دجاج بياض لإنتاج البيض',
        ]);
    }

    private function createChickenCoops(Farm $farm): array
    {
        $existingCoops = FarmUnit::where('farm_id', $farm->id)
            ->where('unit_type', 'house')
            ->count();

        if ($existingCoops > 0) {
            $this->warn("   Farm already has {$existingCoops} عنابر, skipping coop creation.");

            return FarmUnit::where('farm_id', $farm->id)
                ->where('unit_type', 'house')
                ->get()
                ->all();
        }

        $coopCount = (int) $this->ask('How many chicken coops to create?', 3);
        $units = [];

        for ($i = 1; $i <= $coopCount; $i++) {
            $unit = FarmUnit::create([
                'farm_id' => $farm->id,
                'code' => "HOUSE-{$farm->code}-{$i}",
                'name' => "عنبر {$i}",
                'unit_type' => 'house',
                'capacity' => rand(500, 1000),
                'status' => 'active',
            ]);

            $units[] = $unit;
        }

        return $units;
    }

    private function createPoultryBatch(
        Farm $farm,
        Species $species,
        array $units,
        int $birdCount,
        int $days
    ): Batch {
        $batchCode = "POULTRY-{$farm->code}-".now()->format('Ymd');

        // Check for existing active batch
        $existingBatch = Batch::where('farm_id', $farm->id)
            ->where('cycle_type', BatchCycleType::Poultry)
            ->where('is_cycle_closed', false)
            ->first();

        if ($existingBatch) {
            $this->warn("   Found active batch {$existingBatch->batch_code}, using it.");
            $existingBatch->units()->sync(collect($units)->pluck('id'));

            return $existingBatch;
        }

        $unitCost = (float) $this->ask('Cost per bird (EGP)', 25);
        $initialQuantity = $birdCount;

        // Distribute birds across coops
        $birdsPerCoop = (int) ($initialQuantity / count($units));

        $batch = Batch::create([
            'batch_code' => $batchCode,
            'farm_id' => $farm->id,
            'species_id' => $species->id,
            'entry_date' => now()->subDays($days),
            'initial_quantity' => $initialQuantity,
            'current_quantity' => $initialQuantity,
            'unit_cost' => $unitCost,
            'total_cost' => $initialQuantity * $unitCost,
            'source' => BatchSource::Hatchery,
            'status' => BatchStatus::Active,
            'cycle_type' => BatchCycleType::Poultry,
            'notes' => "مزرعة دواجن - {$farm->name}",
        ]);

        // Attach units to batch
        $batch->units()->sync(collect($units)->pluck('id'));

        return $batch;
    }

    private function generateProductionRecords(
        Batch $batch,
        Farm $farm,
        array $units,
        int $days,
        float $eggRate,
        float $mortalityRate = 2.0
    ): int {
        $user = User::first() ?? User::factory()->create();
        $recordCount = 0;

        // Expected eggs per bird per day (layers produce ~1 egg/day at peak)
        $eggsPerBirdPerDay = $eggRate / 100;

        for ($day = $days; $day >= 0; $day--) {
            $date = now()->subDays($day);

            // Skip some days randomly to simulate real-world variation
            if (rand(1, 10) <= 1) {
                continue; // ~10% chance of no record for a day
            }

            foreach ($units as $index => $unit) {
                // Calculate birds in this coop (accounting for mortality)
                $daysIntoBatch = $days - $day;
                $mortalitySoFar = (int) ($batch->initial_quantity * ($mortalityRate / 100) * ($daysIntoBatch / $days));
                $birdsInCoop = (int) ($batch->initial_quantity / count($units)) - (int) ($mortalitySoFar / count($units));
                $birdsInCoop = max(0, $birdsInCoop);

                // Calculate eggs for this coop
                $expectedEggs = (int) ($birdsInCoop * $eggsPerBirdPerDay);

                // Add some variation (+/- 10%)
                $variation = rand(90, 110) / 100;
                $actualEggs = max(0, (int) ($expectedEggs * $variation));

                if ($actualEggs <= 0) {
                    continue;
                }

                // Determine unit type for record
                $unitForRecord = $units[$index % count($units)];

                ProductionRecord::create([
                    'batch_id' => $batch->id,
                    'farm_id' => $farm->id,
                    'unit_id' => $unitForRecord->id,
                    'date' => $date,
                    'quantity' => $actualEggs,
                    'unit' => 'egg',
                    'quality_grade' => $this->getRandomQualityGrade(),
                    'recorded_by' => $user->id,
                    'notes' => "إنتاج يوم {$date->format('Y-m-d')} - عنبر {$unitForRecord->name}",
                ]);

                $recordCount++;
            }
        }

        return $recordCount;
    }

    private function generateMortalityRecords(
        Batch $batch,
        Farm $farm,
        array $units,
        int $days,
        float $mortalityRate
    ): int {
        $user = User::first() ?? User::factory()->create();
        $recordCount = 0;

        // Total expected deaths
        $mortalityRateValue = $mortalityRate;
        $expectedDeaths = (int) ($batch->initial_quantity * ($mortalityRateValue / 100));

        // Distribute deaths across days
        $mortalityReasons = [
            'حرارة' => 30,
            'مرض' => 20,
            'افتراس' => 10,
            'اختناق' => 15,
            'ضغوط' => 15,
            'أسباب أخرى' => 10,
        ];

        for ($i = 0; $i < $expectedDeaths; $i++) {
            // Random day within the period
            $day = rand(0, $days);
            $date = now()->subDays($day);

            // Random unit
            $unit = $units[array_rand($units)];

            // Select reason based on probability
            $reason = $this->weightedRandomChoice($mortalityReasons);

            // Number of deaths per record (usually 1-5)
            $quantity = rand(1, min(5, $expectedDeaths - $recordCount));

            MortalityRecord::create([
                'batch_id' => $batch->id,
                'farm_id' => $farm->id,
                'unit_id' => $unit->id,
                'date' => $date,
                'quantity' => $quantity,
                'reason' => $reason,
                'recorded_by' => $user->id,
                'notes' => "{$reason} - {$quantity} طائر",
            ]);

            $recordCount++;
        }

        return $recordCount;
    }

    private function getRandomQualityGrade(): string
    {
        $grades = [
            'نمرة 1' => 60,
            'نمرة 2' => 25,
            'صغير' => 10,
            'مكسور' => 5,
        ];

        return $this->weightedRandomChoice($grades);
    }

    private function weightedRandomChoice(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        foreach ($weights as $item => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $item;
            }
        }

        return array_key_first($weights);
    }

    private function verifyBatchQuantities(Batch $batch): void
    {
        $totalProduction = ProductionRecord::where('batch_id', $batch->id)->sum('quantity');
        $totalMortality = MortalityRecord::where('batch_id', $batch->id)->sum('quantity');

        // Calculate expected current quantity
        $expectedCurrent = $batch->initial_quantity - $totalMortality;

        $this->newLine();
        $this->info('📊 Batch Summary:');
        $this->line("   Initial birds: {$batch->initial_quantity}");
        $this->line("   Total mortality: {$totalMortality}");
        $this->line("   Expected current: {$expectedCurrent}");
        $this->line("   Total eggs produced: {$totalProduction}");

        if ($batch->current_quantity != $expectedCurrent) {
            $this->warn("   ⚠️  Batch current_quantity ({$batch->current_quantity}) doesn't match expected ({$expectedCurrent})");
            $this->line('       You may want to update it.');
        }
    }
}
