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
        Schema::create('batch_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type'); // entry, transfer, harvest, mortality
            $table->foreignId('from_farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->foreignId('to_farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->foreignId('from_unit_id')->nullable()->constrained('farm_units')->nullOnDelete();
            $table->foreignId('to_unit_id')->nullable()->constrained('farm_units')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('weight', 10, 3)->nullable();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'date']);
            $table->index('movement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_movements');
    }
};
