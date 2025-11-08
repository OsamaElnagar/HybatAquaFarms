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

            // Operations
            PettyCashSeeder::class,
            BatchSeeder::class,
            BatchMovementSeeder::class,
            FeedStockSeeder::class,

            // Transactions
            PostingRuleSeeder::class,
            VoucherSeeder::class,
            SalesOrderSeeder::class,
            EmployeeAdvanceSeeder::class,
            FeedMovementSeeder::class,
            DailyFeedIssueSeeder::class,
        ]);
    }
}
