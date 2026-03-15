<?php

namespace App\Console\Commands;

use App\Enums\TraderStatementStatus;
use App\Models\JournalEntry;
use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTraderStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-trader-statements';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Link existing orphaned JournalEntries to a Trader Statement session.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traders = Trader::all();
        $this->info("Starting migration for {$traders->count()} traders...");

        foreach ($traders as $trader) {
            $this->comment("Processing Trader: {$trader->name}");

            if (! $trader->account_id) {
                $this->warn("Skipping {$trader->name}: No GL account assigned.");

                continue;
            }

            // check if trader has any orphaned entries
            $orphanedEntries = JournalEntry::whereNull('trader_statement_id')
                ->whereHas('lines', fn ($q) => $q->where('account_id', $trader->account_id))
                ->get();

            if ($orphanedEntries->isEmpty()) {
                $this->line("No orphaned entries for {$trader->name}.");

                continue;
            }

            // Find or create an "Initial Session"
            $statement = $trader->statements()->where('status', TraderStatementStatus::Open)->first();

            if (! $statement) {
                $statement = $trader->statements()->create([
                    'title' => 'البداية (معالجة تلقائية)',
                    'opened_at' => $orphanedEntries->min('date') ?? now()->toDateString(),
                    'opening_balance' => 0,
                    'status' => TraderStatementStatus::Open,
                    'notes' => 'جلسة افتتاحية تم إنشاؤها تلقائياً لربط العمليات السابقة.',
                ]);
            }

            $this->info("Linking {$orphanedEntries->count()} entries to statement #{$statement->id}");

            DB::transaction(function () use ($orphanedEntries, $statement) {
                foreach ($orphanedEntries as $entry) {
                    $entry->update(['trader_statement_id' => $statement->id]);
                }
            });
        }

        $this->info('Migration completed successfully!');
    }
}
