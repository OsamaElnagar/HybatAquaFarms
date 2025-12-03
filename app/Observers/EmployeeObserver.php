<?php

namespace App\Observers;

use App\Models\Employee;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        if (! $employee->employee_number) {
            $employee->employee_number = static::generateEmployeeNumber();
        }
    }

    protected static function generateEmployeeNumber(): string
    {
        $lastEmployee = Employee::latest('id')->first();
        $number = $lastEmployee ? ((int) substr($lastEmployee->employee_number, 4)) + 1 : 1;

        return 'EMP-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
