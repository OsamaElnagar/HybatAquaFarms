<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Override the method globally for all Create pages
        CreateRecord::macro('hasCombinedRelationManagerTabsWithContent', function () {
            return true;
        });

        // Override the method globally for all Edit pages
        EditRecord::macro('hasCombinedRelationManagerTabsWithContent', function () {
            return true;
        });
    }
}
