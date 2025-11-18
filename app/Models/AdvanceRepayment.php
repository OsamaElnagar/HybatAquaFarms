<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceRepayment extends Model
{
    /** @use HasFactory<\Database\Factories\AdvanceRepaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_advance_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'salary_record_id',
        'balance_remaining',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount_paid' => 'decimal:2',
            'balance_remaining' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function employeeAdvance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAdvance::class);
    }

    public function salaryRecord(): BelongsTo
    {
        return $this->belongsTo(SalaryRecord::class);
    }
}
