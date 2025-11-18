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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('national_id')->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('status')->default('active')->comment('active/inactive/terminated');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
