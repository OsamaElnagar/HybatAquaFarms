<?php

namespace App\Models;

use App\Enums\AdvanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAdvance extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeAdvanceFactory> */
    use HasFactory;

    protected $fillable = [
        'advance_number',
        'employee_id',
        'request_date',
        'amount',
        'reason',
        'approval_status',
        'approved_by',
        'approved_date',
        'disbursement_date',
        'installments_count',
        'installment_amount',
        'balance_remaining',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'approved_date' => 'date',
            'disbursement_date' => 'date',
            'amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'balance_remaining' => 'decimal:2',
            'status' => AdvanceStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(AdvanceRepayment::class);
    }
}
