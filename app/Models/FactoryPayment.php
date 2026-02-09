<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Observers\FactoryPaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FactoryPaymentObserver::class])]
class FactoryPayment extends Model
{
    /** @use HasFactory<\Database\Factories\FactoryPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'factory_id',
        'farm_id',
        'date',
        'amount',
        'payment_method',
        'reference_number',
        'description',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
