<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Observers\SalaryRecordObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([SalaryRecordObserver::class])]
class SalaryRecord extends Model
{
    /** @use HasFactory<\Database\Factories\SalaryRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'bonuses',
        'deductions',
        'advances_deducted',
        'net_salary',
        'payment_date',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pay_period_start' => 'date',
            'pay_period_end' => 'date',
            'payment_date' => 'date',
            'basic_salary' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'deductions' => 'decimal:2',
            'advances_deducted' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'status' => SalaryStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function advanceRepayments(): HasMany
    {
        return $this->hasMany(AdvanceRepayment::class);
    }
}
