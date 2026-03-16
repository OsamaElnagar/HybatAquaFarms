<?php

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\Pages\ViewFactory;
use App\Models\Account;
use App\Models\Factory;
use App\Models\JournalEntry;
use App\Models\PostingRule;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);

    // Create Treasury account
    $this->treasuryAccount = Account::factory()->create([
        'code' => '29',
        'name' => 'الخزينة',
        'is_treasury' => true,
    ]);

    // Create factory account
    $this->factoryAccount = Account::factory()->create([
        'code' => '2110.REC',
        'name' => 'حساب مصنع',
    ]);

    // Create Required Seed Inventory account for other migrations
    Account::factory()->create(['code' => '1200', 'name' => 'مخزون الزريعة']);

    // Create PostingRule
    PostingRule::updateOrCreate(
        ['event_key' => 'factory.receipt'],
        [
            'name' => 'Factory Receipt',
            'description' => 'استلام مبلغ من مصنع/مفرخ/مورد',
            'debit_account_id' => $this->treasuryAccount->id,
            'is_active' => true,
        ]
    );
});

test('it can receive payment from a factory via action', function () {
    $factory = Factory::factory()->create([
        'type' => FactoryType::FEEDS,
        'account_id' => $this->factoryAccount->id,
    ]);

    $statement = $factory->activeStatement;

    Livewire::test(ViewFactory::class, ['record' => $factory->getRouteKey()])
        ->callAction('receivePayment', [
            'date' => now()->toDateString(),
            'amount' => 5000,
            'treasury_account_id' => $this->treasuryAccount->id,
            'description' => 'قرض من مصنع',
        ])
        ->assertHasNoActionErrors();

    // Check Journal Entry
    $entry = JournalEntry::where('source_type', Factory::class)
        ->where('source_id', $factory->id)
        ->where('factory_statement_id', $statement->id)
        ->with('lines')
        ->first();

    expect($entry)->not->toBeNull();

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    expect($debitLine->account_id)->toBe($this->treasuryAccount->id)
        ->and($debitLine->debit)->toBe(5000.0)
        ->and($creditLine->account_id)->toBe($this->factoryAccount->id)
        ->and($creditLine->credit)->toBe(5000.0);
});
