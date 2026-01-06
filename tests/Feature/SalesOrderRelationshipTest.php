<?php

use App\Models\Batch;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\HarvestOperation;
use App\Models\Order;
use App\Models\SalesOrder;

test('sales order can retrieve harvests via pivot table', function () {
    // 1. Setup Data
    $farm = Farm::factory()->create();
    $batch = Batch::factory()->create(['farm_id' => $farm->id]);

    // Create HarvestOperation manually (since factory might be missing)
    $harvestOperation = HarvestOperation::create([
        'operation_number' => 'OP-TEST-'.rand(1000, 9999),
        'batch_id' => $batch->id,
        'farm_id' => $farm->id,
        'start_date' => now(),
        'status' => 'ongoing',
    ]);

    // Create Harvest
    $harvest = Harvest::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
    ]);

    // Create Order linked to Harvest
    $order = Order::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
        'harvest_id' => $harvest->id,
    ]);

    // Create SalesOrder linked to HarvestOperation
    $salesOrder = SalesOrder::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
    ]);

    // Attach Order to SalesOrder
    $salesOrder->orders()->attach($order);

    // 2. Verify Relationships

    // Check harvests()
    expect($salesOrder->harvests()->count())->toBe(1);
    expect($salesOrder->harvests()->first()->id)->toBe($harvest->id);

    // Check harvestOperations()
    expect($salesOrder->harvestOperations()->count())->toBe(1);
    expect($salesOrder->harvestOperations()->first()->id)->toBe($harvestOperation->id);

    // 3. Verify multiple orders from same harvest count as 1 distinct harvest?
    // The implementation uses distinct() if we used joins, but here we used whereIn which selects unique Harvests by definition.
    // Let's add another order from the same harvest.
    $order2 = Order::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
        'harvest_id' => $harvest->id,
    ]);
    $salesOrder->orders()->attach($order2);

    expect($salesOrder->harvests()->count())->toBe(1); // Should still be 1 harvest

    // Add another harvest
    $harvest2 = Harvest::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
    ]);
    $order3 = Order::factory()->create([
        'harvest_operation_id' => $harvestOperation->id,
        'harvest_id' => $harvest2->id,
    ]);
    $salesOrder->orders()->attach($order3);

    expect($salesOrder->harvests()->count())->toBe(2);
});
