<?php

use App\Models\Driver;
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
        Schema::table('batch_fish', function (Blueprint $table) {
            $table->foreignIdFor(Driver::class)->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_fish', function (Blueprint $table) {
            $table->dropForeignIdFor(Driver::class);
        });
    }
};
