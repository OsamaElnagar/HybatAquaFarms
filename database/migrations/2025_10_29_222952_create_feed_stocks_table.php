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
        Schema::create('feed_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_in_stock', 12, 3)->default(0);
            $table->decimal('average_cost', 10, 2)->nullable()->default(0);
            $table->decimal('total_value', 14, 2)->nullable()->default(0);
            $table->timestamps();

            $table->unique(['feed_warehouse_id', 'feed_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_stocks');
    }
};
