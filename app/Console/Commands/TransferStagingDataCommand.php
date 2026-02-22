<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferStagingDataCommand extends Command
{
    protected $signature = 'app:transfer-staging-data';

    protected $description = 'Transfer all data from the staging SQLite database to the default database';

    public function handle()
    {
        $this->info('Starting data transfer from staging database...');

        $stagingConnection = DB::connection('stagging-2026-02-22');
        $defaultConnection = DB::connection('sqlite');

        // Disable foreign key constraints on the default connection to avoid insertion order issues
        $defaultConnection->statement('PRAGMA foreign_keys = OFF;');

        // Get all tables from the staging database
        $tables = $stagingConnection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $totalTables = count($tables);
        $this->info("Found {$totalTables} tables to process.");

        foreach ($tables as $tableRow) {
            $table = $tableRow->name;

            if ($table === 'migrations') {
                $this->line("Skipping 'migrations' table.");

                continue;
            }

            $this->info("Processing table: {$table}");

            // Truncate table before insert
            $defaultConnection->table($table)->truncate();

            // Fetch all records from staging
            $records = $stagingConnection->table($table)->get()->map(function ($record) {
                return (array) $record;
            })->toArray();

            if (empty($records)) {
                $this->line("  - Table '{$table}' is empty. Skipping.");

                continue;
            }

            // Insert records in chunks
            $chunks = array_chunk($records, 500);

            $this->output->progressStart(count($chunks));

            foreach ($chunks as $chunk) {
                $defaultConnection->table($table)->insert($chunk);
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->line('  - Inserted '.count($records)." records into '{$table}'.");
        }

        // Re-enable foreign key constraints
        $defaultConnection->statement('PRAGMA foreign_keys = ON;');

        $this->info('Data transfer completed successfully.');
    }
}
