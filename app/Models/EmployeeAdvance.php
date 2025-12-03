<?php

namespace App\Models;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Observers\EmployeeAdvanceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([EmployeeAdvanceObserver::class])]
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
            'approval_status' => AdvanceApprovalStatus::class,
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

    #[Scope]
    public function pendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', AdvanceApprovalStatus::PENDING);
    }

    #[Scope]
    public function approved(Builder $query): Builder
    {
        return $query->where('approval_status', AdvanceApprovalStatus::APPROVED);
    }

    #[Scope]
    public function rejected(Builder $query): Builder
    {
        return $query->where('approval_status', AdvanceApprovalStatus::REJECTED);
    }
}
