<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnableFilamentCombinedTabs extends Command
{
    protected $signature = 'filament:enable-combined-tabs {--dry-run}';

    protected $description = 'Add hasCombinedRelationManagerTabsWithContent() to Filament resource pages extending ViewRecord or EditRecord.';

    public function handle(): int
    {
        $files = glob(app_path('Filament/Resources/*/Pages/*.php'));

        if (! $files) {
            $this->info('No matching Filament resource page files found.');

            return static::SUCCESS;
        }

        $method = <<<'PHP'

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

PHP;

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                $this->warn("Could not read file: {$file}");

                continue;
            }

            if (
                ! str_contains($content, 'ViewRecord')
                && ! str_contains($content, 'EditRecord')
            ) {
                continue;
            }

            if (str_contains($content, 'hasCombinedRelationManagerTabsWithContent')) {
                continue;
            }

            if (! preg_match('/}\s*$/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $this->warn("Could not find closing class brace in: {$file}");

                continue;
            }

            $position = $matches[0][1];

            $updatedContent = substr($content, 0, $position).$method."}\n";

            if ($this->option('dry-run')) {
                $this->line("Would update: {$file}");

                continue;
            }

            if (file_put_contents($file, $updatedContent) === false) {
                $this->warn("Failed to write updated content to: {$file}");

                continue;
            }

            $this->info("Updated: {$file}");
        }

        return static::SUCCESS;
    }
}

