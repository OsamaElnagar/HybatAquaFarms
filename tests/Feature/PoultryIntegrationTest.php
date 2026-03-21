<?php

use App\Models\Box;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\MortalityRecord;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionRecord;
use App\Models\Species;
use App\Models\User;
use Database\Factories\BatchFactory;

test('it can record poultry production', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $batch = BatchFactory::new()->create(['farm_id' => $farm->id]);
    $unit = FarmUnit::factory()->create(['farm_id' => $farm->id]);
    $batch->units()->attach($unit);

    $record = ProductionRecord::create([
        'batch_id' => $batch->id,
        'farm_id' => $farm->id,
        'unit_id' => $unit->id,
        'date' => now(),
        'quantity' => 150,
        'unit' => 'tray',
        'recorded_by' => $user->id,
    ]);

    expect($record->exists)->toBeTrue();
    expect($record->quantity)->toEqual(150);
    expect($record->unit)->toBe('tray');
    expect($batch->productionRecords)->toHaveCount(1);
});

test('it can record poultry mortality', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $batch = BatchFactory::new()->create(['farm_id' => $farm->id]);
    $unit = FarmUnit::factory()->create(['farm_id' => $farm->id]);
    $batch->units()->attach($unit);

    $record = MortalityRecord::create([
        'batch_id' => $batch->id,
        'farm_id' => $farm->id,
        'unit_id' => $unit->id,
        'date' => now(),
        'quantity' => 12,
        'reason' => 'Disease',
        'recorded_by' => $user->id,
    ]);

    expect($record->exists)->toBeTrue();
    expect($record->quantity)->toBe(12);
    expect($batch->mortalityRecords)->toHaveCount(1);
});

test('order item calculates subtotal by quantity when price_type is unit', function () {
    $order = Order::factory()->create();
    $box = Box::factory()->for(Species::factory())->create();

    $item = new OrderItem([
        'order_id' => $order->id,
        'box_id' => $box->id,
        'quantity' => 10,
        'unit_price' => 50.00,
    ]);
    $item->price_type = 'unit';
    $item->save();

    expect((float) $item->subtotal)->toEqual(500.00); // 10 * 50
});

test('order item calculates subtotal by weight when price_type is weight', function () {
    $order = Order::factory()->create();
    $box = Box::factory()->for(Species::factory())->create();

    $item = OrderItem::create([
        'order_id' => $order->id,
        'box_id' => $box->id,
        'quantity' => 5,
        'weight_per_box' => 20,
        'unit_price' => 100.00,
    ]);
    $item->price_type = 'weight';
    $item->save();

    expect((float) $item->total_weight)->toEqual(100.00); // 5 * 20
    expect((float) $item->subtotal)->toEqual(10000.00); // 100 * 100
});
