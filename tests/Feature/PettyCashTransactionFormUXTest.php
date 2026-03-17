<?php

use App\Enums\BatchCycleType;
use App\Filament\Resources\Farms\RelationManagers\PettyCashTransactionsRelationManager;
use App\Filament\Resources\PettyCashes\RelationManagers\TransactionsRelationManager;
use App\Models\Batch;
use App\Models\Employee;
use App\Models\Farm;
use App\Models\PettyCash;
use App\Models\Species;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create([
        'email' => 'admin@hybataquafarm.com',
        'email_verified_at' => now(),
    ]));
});

test('petty_cash_id defaults correctly on PettyCash relation manager and updates current_balance', function () {
    $employee = Employee::factory()->create();
    $pettyCash = PettyCash::factory()->create([
        'custodian_employee_id' => $employee->id,
        'opening_balance' => 5000,
    ]);

    Livewire::test(TransactionsRelationManager::class, [
        'ownerRecord' => $pettyCash,
    ])
        ->assertFormFieldValue('petty_cash_id', $pettyCash->id)
        ->assertFormFieldValue('current_balance', '5,000');
});

test('farm_id defaults correctly on Farm relation manager and petty_cash_id is null', function () {
    $farm = Farm::factory()->create();

    Livewire::test(PettyCashTransactionsRelationManager::class, [
        'ownerRecord' => $farm,
    ])
        ->assertFormFieldValue('farm_id', $farm->id)
        ->assertFormFieldValue('petty_cash_id', null);
});

test('dependent fields update automatically when petty_cash_id is set via default', function () {
    $farm = Farm::factory()->create();
    $employee = Employee::factory()->create();
    $species = Species::factory()->create();

    $pettyCash = PettyCash::factory()->create([
        'custodian_employee_id' => $employee->id,
    ]);
    $pettyCash->farms()->attach($farm);

    // Create a main batch
    $batch = Batch::factory()->create([
        'farm_id' => $farm->id,
        'species_id' => $species->id,
        'cycle_type' => BatchCycleType::Main,
        'is_cycle_closed' => false,
    ]);

    Livewire::test(TransactionsRelationManager::class, [
        'ownerRecord' => $pettyCash,
    ])
        ->assertFormFieldValue('petty_cash_id', $pettyCash->id)
        ->assertFormFieldValue('farm_id', $farm->id)
        ->assertFormFieldValue('batch_id', $batch->id);
});
