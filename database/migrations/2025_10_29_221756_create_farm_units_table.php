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
        Schema::create('farm_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // Like "حوض-1" or "POND-A1"
            $table->string('unit_type'); // pond, tank, cage (stored as string, Laravel Enum in code)
            $table->integer('capacity')->nullable();
            $table->string('status')->default('active'); // active, inactive, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_units');
    }
};
