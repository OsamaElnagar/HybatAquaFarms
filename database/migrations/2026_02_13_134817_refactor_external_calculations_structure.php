<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Safe Rename: Only rename if destination doesn't exist and source does
        if (!Schema::hasTable('external_calculation_entries') && Schema::hasTable('external_calculations')) {
            Schema::rename('external_calculations', 'external_calculation_entries');
        }

        // 2. Safe Create Parent: Drop if exists (assuming it's a failed attempt artifact if entries table exists) then Create
        if (Schema::hasTable('external_calculation_entries')) {
            Schema::dropIfExists('external_calculations');
        }

        if (!Schema::hasTable('external_calculations')) {
            Schema::create('external_calculations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Safe Add Column: Add external_calculation_id to entries if missing
        if (Schema::hasTable('external_calculation_entries') && !Schema::hasColumn('external_calculation_entries', 'external_calculation_id')) {
            Schema::table('external_calculation_entries', function (Blueprint $table) {
                $table->foreignId('external_calculation_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // 4. Data Migration: Create a default parent and link existing entries
        if (Schema::hasTable('external_calculation_entries') && DB::table('external_calculation_entries')->count() > 0) {
            // Find or Create the general account
            $parent = DB::table('external_calculations')->where('name', 'General External Account')->first();
            
            if (!$parent) {
                $parentId = DB::table('external_calculations')->insertGetId([
                    'name' => 'General External Account',
                    'description' => 'Automatically created during migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $parentId = $parent->id;
            }

            // Assign this parent to ALL entries that don't have one
            if (Schema::hasColumn('external_calculation_entries', 'external_calculation_id')) {
                DB::table('external_calculation_entries')
                    ->whereNull('external_calculation_id')
                    ->update(['external_calculation_id' => $parentId]);
            }
        }

        // 5. Make external_calculation_id required (Safe check)
        if (Schema::hasTable('external_calculation_entries') && 
            Schema::hasColumn('external_calculation_entries', 'external_calculation_id') &&
            DB::table('external_calculation_entries')->whereNull('external_calculation_id')->doesntExist()) {
            
            Schema::table('external_calculation_entries', function (Blueprint $table) {
                $table->foreignId('external_calculation_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        // Note: Down method might be lossy if we just blindly revert, but standard logic follows:
        if (Schema::hasTable('external_calculation_entries')) {
            if (Schema::hasColumn('external_calculation_entries', 'external_calculation_id')) {
                Schema::table('external_calculation_entries', function (Blueprint $table) {
                    $table->dropForeign(['external_calculation_id']); // This might fail on SQLite but standard syntax
                    $table->dropColumn('external_calculation_id');
                });
            }
            
            Schema::dropIfExists('external_calculations');
            Schema::rename('external_calculation_entries', 'external_calculations');
        }
    }
};
