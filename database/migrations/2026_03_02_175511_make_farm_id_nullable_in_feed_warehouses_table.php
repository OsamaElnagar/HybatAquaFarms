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
        Schema::table('feed_warehouses', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->foreignId('farm_id')->nullable()->change();
            $table->foreign('farm_id')->references('id')->on('farms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_warehouses', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->foreignId('farm_id')->nullable(false)->change();
            $table->foreign('farm_id')->references('id')->on('farms')->cascadeOnDelete();
        });
    }
};
