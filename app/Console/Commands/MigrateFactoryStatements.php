<?php

namespace App\Console\Commands;

use App\Domain\Accounting\PostingService;
use App\Enums\FactoryStatementStatus;
use App\Models\Account;
use App\Models\BatchFish;
use App\Models\BatchPayment;
use App\Models\Factory;
use App\Models\JournalEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateFactoryStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-factory-statements {--post-purchases : Post missing seed purchases (BatchFish) to the ledger}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Link existing orphaned JournalEntries to a Factory Statement session and cleanup Seed Factory accounting.';

    public function __construct(protected PostingService $posting)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payablesAccountId = Account::where('code', '2110')->first()?->id;
        $factories = Factory::all();
        $this->info("Starting migration for {$factories->count()} factories...");

        foreach ($factories as $factory) {
            $this->comment("Processing Factory: {$factory->name}");

            if (! $factory->account_id) {
                $this->warn("Skipping {$factory->name}: No GL account assigned.");

                continue;
            }

            // Cleanup 1: Re-assign BatchPayment lines from general 2110 to specific sub-account
            if ($payablesAccountId) {
                $updatedPayments = DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->join('batch_payments', 'journal_entries.source_id', '=', 'batch_payments.id')
                    ->where('journal_entries.source_type', 'App\Models\BatchPayment')
                    ->where('journal_lines.account_id', $payablesAccountId)
                    ->where('batch_payments.factory_id', $factory->id)
                    ->update(['journal_lines.account_id' => $factory->account_id]);

                if ($updatedPayments > 0) {
                    $this->line("Re-assigned {$updatedPayments} BatchPayment lines for {$factory->name}.");
                }
            }

            // Cleanup 2: Post missing BatchFish purchases (optional)
            if ($this->option('post-purchases')) {
                $unpostedSeeds = BatchFish::where('factory_id', $factory->id)->get();

                foreach ($unpostedSeeds as $seed) {
                    // check if already posted (double check)
                    $exists = JournalEntry::where('source_type', $seed->getMorphClass())
                        ->where('source_id', $seed->id)
                        ->exists();

                    if (! $exists) {
                        $this->info("Posting purchase for Seed #{$seed->id} ({$factory->name})");
                        try {
                            $this->posting->post('seed.purchase', [
                                'amount' => (float) $seed->total_cost,
                                'farm_id' => $seed->batch?->farm_id,
                                'date' => $seed->created_at?->toDateString() ?? now()->toDateString(),
                                'source_type' => $seed->getMorphClass(),
                                'source_id' => $seed->id,
                                'description' => "شراء زريعة (بالتحويل) - {$seed->batch?->batch_code} - {$factory->name}",
                            ]);
                        } catch (\Exception $e) {
                            $this->error("Failed to post for Seed #{$seed->id}: ".$e->getMessage());
                        }
                    }
                }
            }

            // Final step: Link orphaned entries to the active statement
            $orphanedEntryIds = JournalEntry::whereNull('factory_statement_id')
                ->whereHas('lines', fn ($q) => $q->where('account_id', $factory->account_id))
                ->pluck('id');

            if ($orphanedEntryIds->isEmpty()) {
                $this->line("No orphaned entries for {$factory->name}.");

                continue;
            }

            // Find or create an "Initial Session"
            $statement = $factory->statements()->where('status', FactoryStatementStatus::Open)->first();

            if (! $statement) {
                $statement = $factory->statements()->create([
                    'title' => 'البداية (معالجة تلقائية)',
                    'opened_at' => JournalEntry::whereIn('id', $orphanedEntryIds)->min('date') ?? now()->toDateString(),
                    'opening_balance' => 0,
                    'status' => FactoryStatementStatus::Open,
                    'notes' => 'جلسة افتتاحية تم إنشاؤها تلقائياً لربط العمليات السابقة.',
                ]);
            }

            $this->info("Linking {$orphanedEntryIds->count()} entries to statement #{$statement->id}");

            DB::table('journal_entries')
                ->whereIn('id', $orphanedEntryIds)
                ->update(['factory_statement_id' => $statement->id]);
        }

        $this->info('Migration completed successfully!');
    }
}
