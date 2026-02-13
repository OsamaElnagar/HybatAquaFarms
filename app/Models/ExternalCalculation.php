<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalCalculation extends Model
{
    /** @use HasFactory<\Database\Factories\ExternalCalculationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ExternalCalculationEntry::class);
    }
}
