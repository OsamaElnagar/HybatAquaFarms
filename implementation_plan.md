# Treasury Feature & Accounting Integration Plan

## Goal Description

Implement a central "Treasury" dashboard to track the company's real-time cash position. This involves:

1.  **Treasury Definition**: The "Treasury" is the sum of balances of all accounts marked as `is_treasury` (e.g., Cash, Bank, Safes).
2.  **Dashboard**: A custom Filament page displaying these totals and key metrics.
3.  **Accounting Integrity**: Ensuring all money flows (Salaries, Sales, Payments) correctly impact these accounts via the existing `PostingService`.
4.  **Refactoring**: Modernizing the Observer registration pattern across the application.

## User Review Required

> [!IMPORTANT] > **Database Reset**: This plan requires running `php artisan migrate:fresh --seed` (pamfs) because we are modifying an existing migration file. **All local data will be lost.**

## Proposed Changes

### Database

#### [MODIFY] [2025_10_29_221816_create_accounts_table.php](file:///d:/HybatAquaFarms/HybatAquaFarms/database/migrations/2025_10_29_221816_create_accounts_table.php)

-   Add `$table->boolean('is_treasury')->default(false);` to the schema.

#### [MODIFY] [AccountFactory.php](file:///d:/HybatAquaFarms/HybatAquaFarms/database/factories/AccountFactory.php)

-   Add `is_treasury` to the definition (default false).
-   Add a state `treasury()` to easily create treasury accounts in seeders/tests.

### Observers (Refactoring & New)

#### [MODIFY] [AppServiceProvider.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Providers/AppServiceProvider.php)

-   Remove all manual `Model::observe(Observer::class)` calls.

#### [MODIFY] [Models](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models)

-   Add `#[ObservedBy([ObserverClass::class])]` attribute to the following models:
    -   `Voucher`, `FeedMovement`, `DailyFeedIssue`, `FactoryPayment`, `BatchPayment`, `EmployeeAdvance`, `AdvanceRepayment`, `SalesOrder`, `ClearingEntry`, `Batch`, `BatchMovement`.

#### [NEW] [SalaryRecordObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/SalaryRecordObserver.php)

-   Implement `updated` method to post `salary.payment` when status becomes `Paid`.
-   Register via `#[ObservedBy]` on `SalaryRecord` model.

### Filament (UI)

#### [NEW] [TreasuryDashboard.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Filament/Pages/TreasuryDashboard.php)

-   Create a custom page: `php artisan make:filament-page TreasuryDashboard`.
-   Add a "Stats Overview" widget to this page.

#### [NEW] [TreasuryOverview.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Filament/Widgets/TreasuryOverview.php)

-   Create a widget: `php artisan make:filament-widget TreasuryOverview --stats-overview`.
-   **Logic**:
    -   Calculate Total Treasury Balance: `Account::where('is_treasury', true)->get()->sum('balance')`.
    -   Calculate "Incoming Today": Sum of `JournalLine` debits to treasury accounts today.
    -   Calculate "Outgoing Today": Sum of `JournalLine` credits to treasury accounts today.

## Verification Plan

### Automated Tests

-   Run `php artisan test` to ensure refactoring didn't break existing logic.
-   Create `tests/Feature/TreasuryTest.php` to verify:
    -   `is_treasury` flag works.
    -   Salary payment reduces treasury balance.
    -   Dashboard widget calculates totals correctly.

### Manual Verification

1.  **Reset**: Run `pamfs`.
2.  **Setup**: Create a "Main Safe" account with `is_treasury = true`.
3.  **Action**: Pay a Salary of 1000.
4.  **Verify**: Check `TreasuryDashboard`. Total Balance should decrease by 1000.
