<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Observers\AdvanceRepaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([AdvanceRepaymentObserver::class])]
class AdvanceRepayment extends Model
{
    /** @use HasFactory<\Database\Factories\AdvanceRepaymentFactory> */
    use HasFactory, Cacheable;

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
