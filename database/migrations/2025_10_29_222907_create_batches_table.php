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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('farm_units')->nullOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->decimal('initial_weight_avg', 10, 3)->nullable();
            $table->decimal('current_weight_avg', 10, 3)->nullable();
            $table->string('source')->nullable()->comment('hatchery/transfer/purchase');
            $table->string('status')->default('active'); // active, harvested, depleted
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
