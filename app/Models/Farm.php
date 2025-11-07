<?php

namespace App\Models;

use App\Enums\FarmStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    /** @use HasFactory<\Database\Factories\FarmFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'size',
        'location',
        'latitude',
        'longitude',
        'capacity',
        'status',
        'established_date',
        'manager_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => FarmStatus::class,
            'established_date' => 'date',
            'size' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(FarmUnit::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function pettyCash(): HasMany
    {
        return $this->hasMany(PettyCash::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function feedWarehouses(): HasMany
    {
        return $this->hasMany(FeedWarehouse::class);
    }
}
