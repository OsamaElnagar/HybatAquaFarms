<?php

use App\Enums\FactoryType;
use App\Models\Factory;
use App\Models\SupplierActivity;

test('it can assign a supplier activity to a factory', function () {
    $activity = SupplierActivity::create(['name' => 'Test Activity']);

    $factory = Factory::factory()->create([
        'type' => FactoryType::SUPPLIER,
        'supplier_activity_id' => $activity->id,
    ]);

    expect($factory->supplierActivity)->not->toBeNull()
        ->and($factory->supplierActivity->name)->toBe('Test Activity');
});

test('it has seeded supplier activities', function () {
    $this->seed(\Database\Seeders\SupplierActivitySeeder::class);

    $activities = SupplierActivity::pluck('name')->toArray();

    expect($activities)->toContain('تبن')
        ->toContain('قش')
        ->toContain('سباخ')
        ->toContain('سابله')
        ->toContain('مواشي')
        ->toContain('أدوية');
});
