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
        Schema::table('salary_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('unpaid_days')->default(0)->after('pay_period_end');
            $table->text('days_off_details')->nullable()->after('unpaid_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_records', function (Blueprint $table) {
            $table->dropColumn(['unpaid_days', 'days_off_details']);
        });
    }
};
