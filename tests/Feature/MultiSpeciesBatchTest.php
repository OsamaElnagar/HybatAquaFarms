<?php

use App\Models\Batch;
use App\Models\BatchFish;
use App\Models\Species;
use App\Models\Farm;
use function Pest\Laravel\{actingAs, assertDatabaseHas};

it('can aggregate multi-species fish data in batch', function () {
    $farm = Farm::factory()->create();
    $species1 = Species::factory()->create(['name' => 'Tilapia']);
    $species2 = Species::factory()->create(['name' => 'Mullet']);

    $batch = Batch::create([
        'batch_code' => 'BATCH-TEST-002',
        'farm_id' => $farm->id,
        'entry_date' => now(),
        'status' => 'active',
    ]);

    $batch->fish()->createMany([
        [
            'species_id' => $species1->id,
            'quantity' => 1000,
            'unit_cost' => 2.50,
            'total_cost' => 2500,
        ],
        [
            'species_id' => $species2->id,
            'quantity' => 2000,
            'unit_cost' => 3.00,
            'total_cost' => 6000,
        ]
    ]);

    $batch->refresh();

    // Check if relationships exist
    expect($batch->fish)->toHaveCount(2);

    // Validate aggregation logic (via Observer)
    expect((int) $batch->initial_quantity)->toBe(3000);
    expect((float) $batch->total_cost)->toBe(8500.0);
});