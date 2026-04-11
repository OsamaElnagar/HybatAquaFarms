<?php

namespace App\Console\Commands;

use App\Models\FactoryPayment;
use App\Models\FeedMovement;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFactoryJournalAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-factory-accounts {--dry-run : Print changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-assign journal lines from general factory account (37) to specific factory accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting repair of factory journal accounts...');
        $generalAccountId = 37; // "الذمم الدائنة - المصانع"

        $feedMovementCount = 0;
        $factoryPaymentCount = 0;
        $errors = 0;

        DB::transaction(function () use ($generalAccountId, &$feedMovementCount, &$factoryPaymentCount, &$errors) {
            // 1. Process Feed Movements (Purchases)
            $entries = JournalEntry::where('source_type', (new FeedMovement)->getMorphClass())->get();

            foreach ($entries as $entry) {
                $movement = FeedMovement::with('factory')->find($entry->source_id);
                if (! $movement || ! $movement->factory) {
                    continue;
                }

                $factoryAccountId = $movement->factory->account_id;
                if (! $factoryAccountId) {
                    $this->warn("Factory {$movement->factory->name} has no account_id. Skipping entry #{$entry->id}");
                    $errors++;
                    continue;
                }

                // Find the credit line pointing to account 37
                $line = JournalLine::where('journal_entry_id', $entry->id)
                    ->where('account_id', $generalAccountId)
                    ->where('credit', '>', 0)
                    ->first();

                if ($line) {
                    if ($this->option('dry-run')) {
                        $this->line("Dry-run: Would update FeedMovement #{$movement->id} (Factory: {$movement->factory->name}) JournalLine #{$line->id} from account 37 to {$factoryAccountId}");
                    } else {
                        $line->update(['account_id' => $factoryAccountId]);
                    }
                    $feedMovementCount++;
                }
            }

            // 2. Process Factory Payments
            $paymentEntries = JournalEntry::where('source_type', (new FactoryPayment)->getMorphClass())->get();

            foreach ($paymentEntries as $entry) {
                $payment = FactoryPayment::with('factory')->find($entry->source_id);
                if (! $payment || ! $payment->factory) {
                    continue;
                }

                $factoryAccountId = $payment->factory->account_id;
                if (! $factoryAccountId) {
                    $this->warn("Factory {$payment->factory->name} has no account_id. Skipping payment entry #{$entry->id}");
                    $errors++;
                    continue;
                }

                // Find the debit line pointing to account 37
                $line = JournalLine::where('journal_entry_id', $entry->id)
                    ->where('account_id', $generalAccountId)
                    ->where('debit', '>', 0)
                    ->first();

                if ($line) {
                    if ($this->option('dry-run')) {
                        $this->line("Dry-run: Would update FactoryPayment #{$payment->id} (Factory: {$payment->factory->name}) JournalLine #{$line->id} from account 37 to {$factoryAccountId}");
                    } else {
                        $line->update(['account_id' => $factoryAccountId]);
                    }
                    $factoryPaymentCount++;
                }
            }
        });

        $status = $this->option('dry-run') ? 'Found' : 'Fixed';
        $this->info("{$status} {$feedMovementCount} feed movements and {$factoryPaymentCount} factory payments.");
        if ($errors > 0) {
            $this->error("Encountered {$errors} errors.");
        }
    }
}
