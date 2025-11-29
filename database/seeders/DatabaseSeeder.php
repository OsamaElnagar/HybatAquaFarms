<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core setup
        $this->call([
            UserSeeder::class,
            AccountSeeder::class,
            SpeciesSeeder::class,
            FeedItemSeeder::class,
            ExpenseCategorySeeder::class,

            // Farm structure
            FarmSeeder::class,
            FarmUnitSeeder::class,
            FeedWarehouseSeeder::class,

            // Partners & People
            EmployeeSeeder::class,
            TraderSeeder::class,
            FactorySeeder::class,
            DriverSeeder::class,

            // Transactions (PostingRuleSeeder must run before operations that create accounting entries)
            PostingRuleSeeder::class,

            // Operations
            PettyCashSeeder::class,
            TreasurySeeder::class,
            BatchSeeder::class,
            BatchMovementSeeder::class,
            FeedStockSeeder::class,
            VoucherSeeder::class,

            // Harvesting (order matters!)
            HarvestOperationSeeder::class, // Must come before HarvestSeeder
            HarvestSeeder::class,          // Creates harvests, units, and boxes
            SalesOrderSeeder::class,       // Assigns boxes to sales orders

            // Other operations
            EmployeeAdvanceSeeder::class,
            SalaryRecordSeeder::class,
            FeedMovementSeeder::class,
            DailyFeedIssueSeeder::class,
            FactoryPaymentSeeder::class,
            BatchPaymentSeeder::class,
        ]);
    }
}
