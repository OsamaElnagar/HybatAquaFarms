<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename existing table to external_calculation_entries
        Schema::rename('external_calculations', 'external_calculation_entries');

        // 2. Create new external_calculations table (Parent)
        Schema::create('external_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Add external_calculation_id to entries
        Schema::table('external_calculation_entries', function (Blueprint $table) {
            $table->foreignId('external_calculation_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 4. Data Migration: Create a default parent and link existing entries
        if (DB::table('external_calculation_entries')->count() > 0) {
            $farmIds = DB::table('external_calculation_entries')->select('farm_id')->distinct()->pluck('farm_id');

            foreach ($farmIds as $farmId) {
                $parentId = DB::table('external_calculations')->insertGetId([
                    'farm_id' => $farmId,
                    'name' => 'General External Account',
                    'description' => 'Automatically created during migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('external_calculation_entries')
                    ->where('farm_id', $farmId)
                    ->update(['external_calculation_id' => $parentId]);
            }
        }

        // 5. Make external_calculation_id required
        Schema::table('external_calculation_entries', function (Blueprint $table) {
            $table->foreignId('external_calculation_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('external_calculation_entries', function (Blueprint $table) {
            $table->dropForeign(['external_calculation_id']);
            $table->dropColumn('external_calculation_id');
        });

        Schema::dropIfExists('external_calculations');

        Schema::rename('external_calculation_entries', 'external_calculations');
    }
};
