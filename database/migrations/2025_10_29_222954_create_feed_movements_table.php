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
        Schema::create('feed_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_type'); // in, out, transfer
            $table->foreignId('feed_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('feed_warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('feed_warehouses')->nullOnDelete();
            $table->date('date');
            $table->decimal('quantity', 12, 3);
            $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_movements');
    }
};
