<?php

namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Facades\Health;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);

        // add striped to all tables
        Table::configureUsing(function (Table $table) {
            return $table
                ->striped();
            // ->deferLoading()
            // ->defaultPaginationPageOption(20);
        });

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
