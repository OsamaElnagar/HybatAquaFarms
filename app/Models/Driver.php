<?php

namespace App\Models;

use App\Filament\Resources\Drivers\Tables\DriversTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory, Cacheable;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'phone2',
        'license_number',
        'license_expiry',
        'vehicle_type',
        'vehicle_plate',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'counterparty');
    }
}
