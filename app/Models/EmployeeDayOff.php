<?php

namespace App\Models;

use Database\Factories\EmployeeDayOffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDayOff extends Model
{
    /** @use HasFactory<EmployeeDayOffFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
