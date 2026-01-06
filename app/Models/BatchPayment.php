<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Observers\BatchPaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([BatchPaymentObserver::class])]
class BatchPayment extends Model
{
    /** @use HasFactory<\Database\Factories\BatchPaymentFactory> */
    use HasFactory, Cacheable;

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception('لا يمكن إضافة مدفوعات لدورة مقفلة');
            }
        });

        static::updating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception('لا يمكن تعديل مدفوعات دورة مقفلة');
            }
        });

        static::deleting(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception('لا يمكن حذف مدفوعات دورة مقفلة');
            }
        });
    }

    protected $fillable = [
        'batch_id',
        'factory_id',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
