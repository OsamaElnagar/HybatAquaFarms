<?php

use Filament\Notifications\Notification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:daily-sales')->dailyAt('08:00');

Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

Schedule::command('backup:clean')->wednesdays()->at('19:20')
    ->onFailure(fn() => Notification::make()
        ->title('Backup Cleanup Failed')
        ->body('The daily backup cleanup process has failed.')
        ->danger()
        ->icon('heroicon-o-x-circle')
        ->sendToDatabase(\App\Models\User::all()))
    ->onSuccess(fn() => Notification::make()
        ->title('Backup Cleanup Successful')
        ->body('The daily backup cleanup process has completed successfully.')
        ->success()
        ->icon('heroicon-o-check-circle')
        ->sendToDatabase(\App\Models\User::all()));

Schedule::command('backup:run')->dailyAt('19:21')
    ->onFailure(fn() => Notification::make()
        ->title('Backup Failed')
        ->body('The daily backup process has failed.')
        ->danger()
        ->icon('heroicon-o-x-circle')
        ->sendToDatabase(\App\Models\User::all()))
    ->onSuccess(fn() => Notification::make()
        ->title('Backup Successful')
        ->body('The daily backup process has completed successfully.')
        ->success()
        ->icon('heroicon-o-check-circle')
        ->sendToDatabase(\App\Models\User::all()));