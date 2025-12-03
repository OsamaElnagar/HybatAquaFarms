# Treasury Feature Planning

-   [ ] Database Updates <!-- id: 0 -->
    -   [ ] Edit `2025_10_29_221816_create_accounts_table.php` to add `is_treasury` <!-- id: 1 -->
    -   [ ] Update `AccountFactory` to include `is_treasury` state <!-- id: 2 -->
    -   [ ] Run `php artisan migrate:fresh --seed` <!-- id: 3 -->
-   [ ] Observer Refactoring <!-- id: 4 -->
    -   [ ] Refactor `Voucher`, `FeedMovement`, `DailyFeedIssue`, `FactoryPayment`, `BatchPayment`, `EmployeeAdvance`, `AdvanceRepayment`, `SalesOrder`, `ClearingEntry`, `Batch`, `BatchMovement` to use `#[ObservedBy]` <!-- id: 5 -->
    -   [ ] Create and register `SalaryRecordObserver` using `#[ObservedBy]` <!-- id: 6 -->
-   [ ] Treasury Feature Implementation <!-- id: 7 -->
    -   [ ] Create `TreasuryDashboard` page <!-- id: 8 -->
    -   [ ] Create `TreasuryOverview` stats widget <!-- id: 9 -->
    -   [ ] Register widget on the dashboard <!-- id: 10 -->
-   [ ] Verification <!-- id: 11 -->
    -   [ ] Verify Salary payment posts to accounting <!-- id: 12 -->
    -   [ ] Verify Treasury dashboard shows correct totals <!-- id: 13 -->
