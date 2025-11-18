<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'name',
        'phone',
        'phone2',
        'national_id',
        'address',
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

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class);
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
            ->whereIn('status', ['approved', 'partially_paid'])
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
