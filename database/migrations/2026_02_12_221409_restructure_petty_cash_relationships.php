<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create pivot table
        Schema::create('farm_petty_cash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('petty_cash_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['farm_id', 'petty_cash_id']);
        });

        // 2. Add farm_id to petty_cash_transactions
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->after('petty_cash_id')->constrained()->nullOnDelete();
        });

        // 3. Migrate data
        // For each PettyCash, create an entry in farm_petty_cash and update its transactions
        $pettyCashes = DB::table('petty_cashes')->get();

        foreach ($pettyCashes as $pettyCash) {
            if ($pettyCash->farm_id) {
                // Insert into pivot table
                DB::table('farm_petty_cash')->insert([
                    'farm_id' => $pettyCash->farm_id,
                    'petty_cash_id' => $pettyCash->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update transactions
                DB::table('petty_cash_transactions')
                    ->where('petty_cash_id', $pettyCash->id)
                    ->update(['farm_id' => $pettyCash->farm_id]);
            }
        }

        // 4. Drop farm_id from petty_cashes
        Schema::table('petty_cashes', function (Blueprint $table) {
            // Drop the unique constraint first (it depends on farm_id)
            $table->dropUnique(['farm_id', 'name']);
            // Drop foreign key
            $table->dropForeign(['farm_id']);
            // Drop column
            $table->dropColumn('farm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add farm_id back to petty_cashes
        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // 2. Restore data from pivot table (approximate reverse)
        // We take the first farm associated with the petty cash
        $pivots = DB::table('farm_petty_cash')
            ->orderBy('created_at') // arbitrary order
            ->get()
            ->groupBy('petty_cash_id');

        foreach ($pivots as $pettyCashId => $farmLinks) {
            if ($farmLinks->isNotEmpty()) {
                DB::table('petty_cashes')
                    ->where('id', $pettyCashId)
                    ->update(['farm_id' => $farmLinks->first()->farm_id]);
            }
        }

        // Restore unique constraint
        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->unique(['farm_id', 'name']);
        });

        // 3. Drop farm_id from petty_cash_transactions
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->dropColumn('farm_id');
        });

        // 4. Drop pivot table
        Schema::dropIfExists('farm_petty_cash');
    }
};
