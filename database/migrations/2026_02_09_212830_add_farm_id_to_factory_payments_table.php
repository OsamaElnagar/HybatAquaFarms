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
        Schema::table('factory_payments', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->after('factory_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factory_payments', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->dropColumn('farm_id');
        });
    }
};
