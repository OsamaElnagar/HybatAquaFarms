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
        Schema::table('farm_expenses', function (Blueprint $table) {
            $table->foreignId('advance_repayment_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_expenses', function (Blueprint $table) {
            $table->dropForeign(['advance_repayment_id']);
            $table->dropColumn('advance_repayment_id');
        });
    }
};
