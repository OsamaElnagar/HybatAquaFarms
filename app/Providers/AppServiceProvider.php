<?php

namespace App\Providers;

use App\Models\AdvanceRepayment;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\ClearingEntry;
use App\Models\DailyFeedIssue;
use App\Models\EmployeeAdvance;
use App\Models\FeedMovement;
use App\Models\SalesOrder;
use App\Models\Voucher;
use App\Observers\AdvanceRepaymentObserver;
use App\Observers\BatchMovementObserver;
use App\Observers\BatchObserver;
use App\Observers\ClearingEntryObserver;
use App\Observers\DailyFeedIssueObserver;
use App\Observers\EmployeeAdvanceObserver;
use App\Observers\FeedMovementObserver;
use App\Observers\SalesOrderObserver;
use App\Observers\VoucherObserver;
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
        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);

        Voucher::observe(VoucherObserver::class);
        FeedMovement::observe(FeedMovementObserver::class);
        DailyFeedIssue::observe(DailyFeedIssueObserver::class);
        EmployeeAdvance::observe(EmployeeAdvanceObserver::class);
        AdvanceRepayment::observe(AdvanceRepaymentObserver::class);
        SalesOrder::observe(SalesOrderObserver::class);
        ClearingEntry::observe(ClearingEntryObserver::class);
        Batch::observe(BatchObserver::class);
        BatchMovement::observe(BatchMovementObserver::class);
    }
}
