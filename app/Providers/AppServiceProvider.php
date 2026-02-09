<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Facades\Health;

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

        Model::automaticallyEagerLoadRelationships();

        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);

        // add striped to all tables
        Table::configureUsing(function (Table $table) {
            return $table
                ->striped()
                // ->deferLoading()
                ->defaultPaginationPageOption(25);
        });

        CreateAction::configureUsing(function (CreateAction $action) {
            return $action->slideOver()->modalWidth(Width::SixExtraLarge);
        });
        EditAction::configureUsing(function (EditAction $action) {
            return $action->slideOver()->modalWidth(Width::SixExtraLarge);
        });
    }
}
