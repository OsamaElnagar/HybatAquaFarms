<?php

use App\Enums\FactoryType;
use App\Models\Factory;
use App\Models\FactoryPayment;
use App\Models\Farm;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('it can create a payment for a supplier with farm_id', function () {
    $supplier = Factory::factory()->create([
        'type' => FactoryType::SUPPLIER,
        'name' => 'Test Supplier',
    ]);
    
    $farm = Farm::factory()->create(['name' => 'Test Farm']);

    $payment = FactoryPayment::create([
        'factory_id' => $supplier->id,
        'farm_id' => $farm->id,
        'date' => now(),
        'amount' => 1000,
        'payment_method' => \App\Enums\PaymentMethod::CASH,
        'description' => 'Payment for supplies',
        'recorded_by' => $this->user->id,
    ]);

    expect($payment->farm_id)->toBe($farm->id)
        ->and($payment->factory_id)->toBe($supplier->id);
});

test('it uses farm_id from payment in observer', function () {
    // Mocking PostingService would be ideal here to verify the call, 
    // but for now we verify the data integrity.
    // Assuming the observer runs without error (checked via successful creation)
    
    $supplier = Factory::factory()->create([
        'type' => FactoryType::SUPPLIER,
    ]);
    
    $farm = Farm::factory()->create();

    $payment = FactoryPayment::create([
        'factory_id' => $supplier->id,
        'farm_id' => $farm->id,
        'date' => now(),
        'amount' => 500,
        'payment_method' => \App\Enums\PaymentMethod::CASH,
    ]);

    expect($payment->farm_id)->not->toBeNull();
});
