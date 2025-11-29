<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\VoucherFactory> */
    use HasFactory;

    protected $fillable = [
        "farm_id",
        "batch_id",
        "voucher_type",
        "voucher_number",
        "date",
        "counterparty_type",
        "counterparty_id",
        "petty_cash_id",
        "amount",
        "description",
        "payment_method",
        "reference_number",
        "created_by",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "date" => "date",
            "amount" => "decimal:2",
            "voucher_type" => VoucherType::class,
            "payment_method" => PaymentMethod::class,
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function counterparty(): MorphTo
    {
        return $this->morphTo();
    }

    public function pettyCash(): BelongsTo
    {
        return $this->belongsTo(PettyCash::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function pettyCashTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->wasRecentlyCreated) {
                $eventKey = match($model->voucher_type) {
                    \App\Enums\VoucherType::Payment => 'voucher.payment',
                    \App\Enums\VoucherType::Receipt => 'voucher.receipt',
                };
                \App\Services\PostingService::post($model, $eventKey, $model->amount);
            }
        });
    }
}
