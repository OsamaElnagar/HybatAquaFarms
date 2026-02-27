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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['phone2', 'national_id', 'address']);
        });

        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn(['installments_count', 'installment_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('phone2')->nullable();
            $table->string('national_id')->nullable();
            $table->text('address')->nullable();
        });

        Schema::table('employee_advances', function (Blueprint $table) {
            $table->integer('installments_count')->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
        });
    }
};
