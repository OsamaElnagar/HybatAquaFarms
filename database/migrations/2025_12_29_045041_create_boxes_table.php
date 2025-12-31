<?php

use App\Models\Species;
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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignIdFor(Species::class)->constrained()->nullOnDelete();

            // Capacity [Needed to prevent non-logical weight numbers when entering data]
            $table->decimal('max_weight', 10, 2)->nullable(); // kg - maximum capacity
            $table->decimal('class_total_weight', 10, 2)->nullable()->comment('وزن القفص من هذه الفئة');

            // Classification & Quality
            $table->string('class')->nullable()->comment('التصنيف: بلطي، نمرة 1، نمرة 2، جامبو، خرط، إلخ');
            $table->string('category')->nullable()->comment('90,75,.....');

            $table->timestamps();

            $table->index('name');
            $table->index('species_id');
            $table->index('class');
            $table->index('category');
            $table->unique(['name', 'species_id', 'class', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
