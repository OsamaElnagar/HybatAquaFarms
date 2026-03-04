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
    ->onFailure(fn () => Notification::make()
        ->title('فشل تنظيف النسخة الاحتياطية')
        ->body('فشلت عملية تنظيف النسخة الاحتياطية اليومية.')
        ->danger()
        ->icon('heroicon-o-x-circle')
        ->sendToDatabase(\App\Models\User::all()))
    ->onSuccess(fn () => Notification::make()
        ->title('تم تنظيف النسخة الاحتياطية بنجاح')
        ->body('اكتملت عملية تنظيف النسخة الاحتياطية اليومية بنجاح.')
        ->success()
        ->icon('heroicon-o-check-circle')
        ->sendToDatabase(\App\Models\User::all()));

Schedule::command('backup:run')->dailyAt('19:21')
    ->onFailure(fn () => Notification::make()
        ->title('فشل النسخ الاحتياطي')
        ->body('فشلت عملية النسخ الاحتياطي اليومية.')
        ->danger()
        ->icon('heroicon-o-x-circle')
        ->sendToDatabase(\App\Models\User::all()))
    ->onSuccess(fn () => Notification::make()
        ->title('تم النسخ الاحتياطي بنجاح')
        ->body('اكتملت عملية النسخ الاحتياطي اليومية بنجاح.')
        ->success()
        ->icon('heroicon-o-check-circle')
        ->sendToDatabase(\App\Models\User::all()));
