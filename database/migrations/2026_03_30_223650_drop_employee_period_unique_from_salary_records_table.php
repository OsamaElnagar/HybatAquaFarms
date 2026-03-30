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
            $table->dropUnique('salary_records_employee_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_records', function (Blueprint $table) {
            $table->unique(
                ['employee_id', 'pay_period_start', 'pay_period_end'],
                'salary_records_employee_period_unique'
            );
        });
    }
};
