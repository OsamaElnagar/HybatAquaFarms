<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batch_payments', function (Blueprint $table) {
            $table->foreignId('batch_fish_id')->nullable()->after('batch_id')->constrained('batch_fish')->nullOnDelete();
            // We keep factory_id for historical records that don't have batch_fish_id, but we'll make it nullable later if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_payments', function (Blueprint $table) {
            $table->dropForeign(['batch_fish_id']);
            $table->dropColumn('batch_fish_id');
        });
    }
};
