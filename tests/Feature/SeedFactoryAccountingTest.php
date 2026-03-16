<?php

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\BatchFish;
use App\Models\BatchPayment;
use App\Models\Factory;
use App\Models\JournalEntry;
use App\Models\PostingRule;
use App\Models\Species;
use App\Models\User;
use Database\Factories\BatchFactory;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);

    // Create the mandatory inventory account and PostingRule since we are in a clean test DB
    $this->debitAccount = Account::factory()->create(['code' => '1200', 'name' => 'مخزون الزريعة']);

    PostingRule::updateOrCreate(
        ['event_key' => 'seed.purchase'],
        [
            'name' => 'Seed Purchase',
            'description' => 'شراء زريعة من المفرخات',
            'debit_account_id' => $this->debitAccount->id,
            'is_active' => true,
        ]
    );
});

test('it creates a journal entry when a batch fish purchase is recorded with correct credit account', function () {
    // Create a factory with an account
    $factoryAccount = Account::factory()->create(['code' => '2110.TEST']);
    $factory = Factory::factory()->create([
        'type' => FactoryType::SEEDS,
        'name' => 'Test Hatchery',
        'account_id' => $factoryAccount->id,
    ]);

    // Ensure it has an open statement
    $statement = $factory->activeStatement;
    expect($statement)->not->toBeNull();

    $batch = BatchFactory::new()->create();
    $species = Species::factory()->create();

    $batchFish = BatchFish::create([
        'batch_id' => $batch->id,
        'species_id' => $species->id,
        'factory_id' => $factory->id,
        'quantity' => 1000,
        'unit_cost' => 5,
        'total_cost' => 5000,
    ]);

    // Check Journal Entry
    $entry = JournalEntry::where('source_type', BatchFish::class)
        ->where('source_id', $batchFish->id)
        ->with('lines')
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->factory_statement_id)->toBe($statement->id);

    // Verify debit (inventory) and credit (factory)
    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    expect($debitLine->account_id)->toBe($this->debitAccount->id)
        ->and($creditLine->account_id)->toBe($factoryAccount->id);

    // Check Factory Stats
    $activity = $factory->refresh()->current_year_activity;
    expect((float) $activity['purchases'])->toBe(5000.0);
});

test('it creates a journal entry when a batch payment is recorded', function () {
    $factoryAccount = Account::factory()->create(['code' => '2110.PAY']);
    $factory = Factory::factory()->create([
        'type' => FactoryType::SEEDS,
        'name' => 'Test Hatchery',
        'account_id' => $factoryAccount->id,
    ]);

    $statement = $factory->activeStatement;
    $batch = BatchFactory::new()->create();
    $species = Species::factory()->create();

    $batchFish = BatchFish::create([
        'batch_id' => $batch->id,
        'species_id' => $species->id,
        'factory_id' => $factory->id,
        'quantity' => 1000,
        'unit_cost' => 5,
        'total_cost' => 5000,
    ]);

    $payment = BatchPayment::create([
        'batch_id' => $batch->id,
        'batch_fish_id' => $batchFish->id,
        'factory_id' => $factory->id,
        'date' => now(),
        'amount' => 2000,
        'payment_method' => PaymentMethod::CASH,
        'recorded_by' => $this->user->id,
    ]);

    // Check Journal Entry
    $entry = JournalEntry::where('source_type', BatchPayment::class)
        ->where('source_id', $payment->id)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->factory_statement_id)->toBe($statement->id);

    // Check Factory Stats
    $activity = $factory->refresh()->current_year_activity;
    expect((float) $activity['purchases'])->toBe(5000.0)
        ->and((float) $activity['payments'])->toBe(2000.0);
});
