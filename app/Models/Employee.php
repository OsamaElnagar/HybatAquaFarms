<?php

namespace App\Models;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\EmployeeStatementStatus;
use App\Enums\EmployeeStatus;
use App\Observers\EmployeeObserver;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'name',
        'phone',
        'hire_date',
        'termination_date',
        'farm_id',
        'basic_salary',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'termination_date' => 'date',
            'basic_salary' => 'decimal:2',
            'status' => EmployeeStatus::class,
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function activeAdvances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class)
            ->where('status', AdvanceStatus::Active)
            ->where('approval_status', AdvanceApprovalStatus::APPROVED);
    }

    public function advanceRepayments(): HasManyThrough
    {
        return $this->hasManyThrough(
            AdvanceRepayment::class,
            EmployeeAdvance::class,
            'employee_id',
            'employee_advance_id',
            'id',
            'id',
        );
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class);
    }

    public function daysOff(): HasMany
    {
        return $this->hasMany(EmployeeDayOff::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(EmployeeStatement::class);
    }

    public function activeStatement(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatement::class, 'id', 'employee_id')
            ->where('status', EmployeeStatementStatus::Open)
            ->latest();
    }

    public function getActiveStatementAttribute(): ?EmployeeStatement
    {
        return $this->statements()->where('status', EmployeeStatementStatus::Open)->latest()->first();
    }

    public function openNewStatement(?string $title = null, ?string $notes = null): EmployeeStatement
    {
        return DB::transaction(function () use ($title, $notes) {
            $active = $this->active_statement;

            if ($active) {
                $active->update([
                    'status' => EmployeeStatementStatus::Closed,
                    'closed_at' => now(),
                    'closing_balance' => $active->net_balance,
                ]);
            }

            return $this->statements()->create([
                'title' => $title,
                'opened_at' => now(),
                'opening_balance' => $active ? $active->closing_balance : 0,
                'status' => EmployeeStatementStatus::Open,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function custodialPettyCashes(): HasMany
    {
        return $this->hasMany(PettyCash::class, 'custodian_employee_id');
    }

    public function managedFarms(): HasMany
    {
        return $this->hasMany(Farm::class, 'manager_id');
    }

    /**
     * Get total outstanding advances for this employee.
     */
    public function getTotalOutstandingAdvancesAttribute(): float
    {
        return (float) $this->advances()
            ->where('status', AdvanceStatus::Active)
            ->where('approval_status', AdvanceApprovalStatus::APPROVED)
            ->sum('balance_remaining');
    }

    /**
     * Get total salary paid to this employee.
     */
    public function getTotalSalariesPaidAttribute(): float
    {
        return (float) $this->salaryRecords()->sum('net_salary');
    }

    /**
     * Get salary records count.
     */
    public function getSalaryRecordsCountAttribute(): int
    {
        return $this->salaryRecords()->count();
    }

    /**
     * Get advances count.
     */
    public function getAdvancesCountAttribute(): int
    {
        return $this->advances()->count();
    }

    /**
     * Scopes
     */
    #[Scope]
    public function active($query)
    {
        return $query->where('status', 'active');
    }

    #[Scope]
    public function inactive($query)
    {
        return $query->where('status', 'inactive');
    }

    #[Scope]
    public function terminated($query)
    {
        return $query->where('status', 'terminated');
    }
}
