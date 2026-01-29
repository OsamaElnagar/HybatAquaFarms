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
        Schema::create('batch_farm_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['batch_id', 'farm_unit_id']);
        });

        // Migrate existing data
        $batches = DB::table('batches')->whereNotNull('unit_id')->get();
        foreach ($batches as $batch) {
            DB::table('batch_farm_unit')->insert([
                'batch_id' => $batch->id,
                'farm_unit_id' => $batch->unit_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the old column
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the column
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('farm_id')->constrained('farm_units')->nullOnDelete();
        });

        // Restore data
        $pivotData = DB::table('batch_farm_unit')->get();
        foreach ($pivotData as $data) {
            DB::table('batches')
                ->where('id', $data->batch_id)
                ->update(['unit_id' => $data->farm_unit_id]);
        }

        Schema::dropIfExists('batch_farm_unit');
    }
};
