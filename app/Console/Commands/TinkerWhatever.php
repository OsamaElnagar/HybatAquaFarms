<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TinkerWhatever extends Command
{
    protected $signature = 'app:tinker-whatever';

    protected $description = ' Instead of running artisan tanker execute commands that doesn\'t work let\'s add whatever we want over here, And update whenever needed';

    public function handle()
    {
        $this->info('Starting whatever ...');

        $this->info('Verification Completed.');
    }
}
