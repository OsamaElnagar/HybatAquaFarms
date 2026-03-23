<?php

namespace App\Console\Commands;

use App\Enums\BatchCycleType;
use App\Models\Batch;
use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\ProductionRecord;
use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedEggSales extends Command
{
    protected $signature = 'seed:egg-sales
                            {batchId? : Specific batch ID to seed}
                            {--collections=3 : Number of egg collections to create}
                            {--sales-per-collection=2 : Number of sales per collection}
                            {--cash-ratio=0.3 : Ratio of cash sales (0.0 to 1.0)}';

    protected $description = 'Seed egg sales workflow: collections and sales for a poultry batch';

    public function handle(): int
    {
        $collectionsCount = (int) $this->option('collections');
        $salesPerCollection = (int) $this->option('sales-per-collection');
        $cashRatio = (float) $this->option('cash-ratio');

        $this->info('🥚 Starting Egg Sales Seeder...');
        $this->info("   Collections: {$collectionsCount}, Sales per collection: {$salesPerCollection}, Cash ratio: {$cashRatio}");
        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Get or find a poultry batch
            $batch = $this->getOrCreateBatch();
            $this->info("✓ Batch: {$batch->batch_code} (ID: {$batch->id})");

            // Step 2: Get production records for reference
            $productionRecords = ProductionRecord::where('batch_id', $batch->id)->get();
            $totalEggsProduced = $productionRecords->sum('quantity');
            $this->info("   Total eggs produced: {$totalEggsProduced}");

            // Step 3: Get or create traders
            $traders = $this->getOrCreateTraders();
            $this->info('✓ Traders available: '.count($traders));
            $this->info('Traders: '.collect($traders)->map(fn ($trader) => $trader->name)->implode(', '));

            // Step 4: Create egg collections
            $collections = $this->createEggCollections($batch, $collectionsCount);
            $this->info('✓ Created '.count($collections).' egg collections');

            // Step 5: Create egg sales
            $salesStats = $this->createEggSales($collections, $salesPerCollection, $traders, $cashRatio);
            $this->info("✓ Created {$salesStats['total']} egg sales");
            $this->info("   - Cash sales: {$salesStats['cash']}");
            $this->info("   - Trader sales: {$salesStats['trader']}");
            $this->info("   - Total revenue: {$salesStats['revenue']} EGP");

            // Step 6: Summary
            $this->showSummary($batch, $collections, $salesStats);

            DB::commit();

            $this->newLine();
            $this->info('✅ Egg sales workflow seeded successfully!');

            return static::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Seeding failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return static::FAILURE;
        }
    }

    private function getOrCreateBatch(): Batch
    {
        $batchId = $this->argument('batchId');

        if ($batchId) {
            $batch = Batch::find($batchId);
            if (! $batch) {
                throw new \RuntimeException("Batch with ID {$batchId} not found");
            }

            return $batch;
        }

        // Find existing poultry batch
        $existingBatch = Batch::where('cycle_type', BatchCycleType::Poultry)
            ->where('is_cycle_closed', false)
            ->first();

        if ($existingBatch) {
            if ($this->confirm("Found poultry batch '{$existingBatch->batch_code}'. Use it?", true)) {
                return $existingBatch;
            }
        }

        // Find any poultry batch
        $anyBatch = Batch::where('cycle_type', BatchCycleType::Poultry)->first();

        if ($anyBatch) {
            $this->warn("Using existing poultry batch: {$anyBatch->batch_code}");

            return $anyBatch;
        }

        throw new \RuntimeException('No poultry batch found. Run seed:poultry-farm first.');
    }

    private function getOrCreateTraders(): array
    {
        $existingTraders = Trader::take(2)->get();

        if ($existingTraders->count() >= 2) {
            return $existingTraders->all();
        }

        // Create sample traders
        $trader1 = Trader::firstOrCreate(
            ['name' => 'تاجر البيض المركزي'],
            [
                'code' => 'TRADER-001',
                'phone' => '01012345678',
                'address' => 'القاهرة',
            ]
        );

        $trader2 = Trader::firstOrCreate(
            ['name' => 'محلات البيض الكبرى'],
            [
                'code' => 'TRADER-002',
                'phone' => '01098765432',
                'address' => 'الجيزة',
            ]
        );

        return [$trader1, $trader2];
    }

    private function createEggCollections(Batch $batch, int $count): array
    {
        $collections = [];

        // Get production records
        $productionRecords = ProductionRecord::where('batch_id', $batch->id)
            ->orderBy('date')
            ->get();

        if ($productionRecords->isEmpty()) {
            // Generate collections without production data
            $startDate = $batch->entry_date ?? now()->subDays(30);

            for ($i = 0; $i < $count; $i++) {
                $collectionDate = $startDate->copy()->addDays(($i + 1) * 7);
                $trays = rand(5, 20);
                $eggsPerTray = rand(28, 30);
                $eggs = $trays * $eggsPerTray;

                $collection = EggCollection::create([
                    'batch_id' => $batch->id,
                    'farm_id' => $batch->farm_id,
                    'collection_date' => $collectionDate,
                    'total_trays' => $trays,
                    'total_eggs' => $eggs,
                    'quality_grade' => $this->randomQualityGrade(),
                    'notes' => 'تجميع أسبوعي '.($i + 1),
                    'created_by' => auth()->id(),
                ]);

                $collections[] = $collection;
            }

            return $collections;
        }

        // Create collections from production data
        $recordsPerCollection = (int) ceil($productionRecords->count() / $count);
        $batches = $productionRecords->chunk($recordsPerCollection);

        $i = 0;
        foreach ($batches as $recordBatch) {
            $i++;
            $totalTrays = 0;
            $totalEggs = 0;
            $qualityGrades = [];

            foreach ($recordBatch as $record) {
                $trays = rand(1, 5);
                $eggs = $record->quantity;
                $totalTrays += $trays;
                $totalEggs += $eggs;
                $qualityGrades[] = $record->quality_grade ?? $this->randomQualityGrade();
            }

            $firstRecord = $recordBatch->first();
            $collectionDate = $firstRecord->date;

            $collection = EggCollection::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'collection_date' => $collectionDate,
                'total_trays' => $totalTrays,
                'total_eggs' => $totalEggs,
                'quality_grade' => collect($qualityGrades)->mode()[0] ?? 'نمرة 1',
                'notes' => "تجميع رقم {$i}",
                'created_by' => auth()->id(),
            ]);

            $collections[] = $collection;
        }

        return $collections;
    }

    private function createEggSales(array $collections, int $salesPerCollection, array $traders, float $cashRatio): array
    {
        $stats = ['total' => 0, 'cash' => 0, 'trader' => 0, 'revenue' => 0];
        $prices = [30, 35, 40, 45, 50]; // EGP per tray

        foreach ($collections as $collection) {
            $availableTrays = $collection->total_trays;

            for ($i = 0; $i < $salesPerCollection && $availableTrays > 0; $i++) {
                $isCashSale = (rand(1, 100) / 100) <= $cashRatio;
                $traysSold = min(rand(1, 5), $availableTrays);
                $availableTrays -= $traysSold;

                $trader = $isCashSale ? null : $traders[array_rand($traders)];
                $unitPrice = $prices[array_rand($prices)];
                $subtotal = $traysSold * $unitPrice;
                $transportCost = $isCashSale ? 0 : rand(5, 20);
                $discount = rand(0, 1) > 0.7 ? rand(5, 15) : 0;
                $netAmount = $subtotal + $transportCost - $discount;

                EggSale::create([
                    'egg_collection_id' => $collection->id,
                    'batch_id' => $collection->batch_id,
                    'trader_id' => $trader?->id,
                    'is_cash_sale' => $isCashSale,
                    'sale_date' => $collection->collection_date->addDays(rand(0, 2)),
                    'trays_sold' => $traysSold,
                    'eggs_per_tray' => 30,
                    'total_eggs' => $traysSold * 30,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'transport_cost' => $transportCost,
                    'discount_amount' => $discount,
                    'net_amount' => $netAmount,
                    'payment_status' => $isCashSale ? 'paid' : 'pending',
                    'notes' => $isCashSale ? 'بيع نقدي' : "بيع لتاجر: {$trader->name}",
                    'created_by' => auth()->id(),
                ]);

                $stats['total']++;
                $stats['revenue'] += $netAmount;

                if ($isCashSale) {
                    $stats['cash']++;
                } else {
                    $stats['trader']++;
                }
            }
        }

        return $stats;
    }

    private function showSummary(Batch $batch, array $collections, array $stats): void
    {
        $this->newLine();
        $this->info('📊 Egg Sales Summary:');
        $this->line("   Batch: {$batch->batch_code}");
        $this->line('   Collections created: '.count($collections));
        $this->line("   Total sales: {$stats['total']}");

        $totalEggs = collect($collections)->sum('total_eggs');
        $totalTrays = collect($collections)->sum('total_trays');
        $this->line("   Total trays collected: {$totalTrays}");
        $this->line("   Total eggs collected: {$totalEggs}");
        $this->line("   Cash sales: {$stats['cash']}");
        $this->line("   Trader sales: {$stats['trader']}");
        $this->line("   Total revenue: {$stats['revenue']} EGP");
    }

    private function randomQualityGrade(): string
    {
        $grades = [
            'نمرة 1' => 60,
            'نمرة 2' => 25,
            'صغير' => 10,
            'مكسور' => 5,
        ];

        $total = array_sum($grades);
        $random = rand(1, $total);

        foreach ($grades as $grade => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $grade;
            }
        }

        return 'نمرة 1';
    }
}
