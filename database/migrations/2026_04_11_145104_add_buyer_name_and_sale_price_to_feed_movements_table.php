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
        Schema::table('feed_movements', function (Blueprint $table) {
            $table->string('buyer_name', 255)->nullable()->after('driver_id');
            $table->decimal('sale_price', 12, 2)->nullable()->after('buyer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_movements', function (Blueprint $table) {
            //
        });
    }
};
