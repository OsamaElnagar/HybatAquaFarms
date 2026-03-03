<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:daily-sales')->dailyAt('08:00');
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
Schedule::command('backup:clean')->daily()->at('19:12');
Schedule::command('backup:run')->daily()->at('19:13');