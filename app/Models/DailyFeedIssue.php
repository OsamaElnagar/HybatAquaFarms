<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyFeedIssue extends Model
{
    /** @use HasFactory<\Database\Factories\DailyFeedIssueFactory> */
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن إضافة صرف علف لدورة مقفلة");
            }
        });

        static::updating(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن تعديل صرف علف لدورة مقفلة");
            }
        });

        static::deleting(function ($model) {
            if ($model->batch && $model->batch->is_cycle_closed) {
                throw new \Exception("لا يمكن حذف صرف علف لدورة مقفلة");
            }
        });
    }

    protected $fillable = [
        "farm_id",
        "unit_id",
        "feed_item_id",
        "feed_warehouse_id",
        "date",
        "quantity",
        "batch_id",
        "recorded_by",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "date" => "date",
            "quantity" => "decimal:3",
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(FarmUnit::class, "unit_id");
    }

    public function feedItem(): BelongsTo
    {
        return $this->belongsTo(FeedItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(FeedWarehouse::class, "feed_warehouse_id");
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "recorded_by");
    }
}
