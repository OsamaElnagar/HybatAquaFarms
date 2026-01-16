<?php

namespace App\Models;

use App\Enums\FeedMovementType;
use App\Observers\FeedMovementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FeedMovementObserver::class])]
class FeedMovement extends Model
{
    /** @use HasFactory<\Database\Factories\FeedMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'movement_type',
        'feed_item_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'date',
        'quantity',
        'factory_id',
        'driver_id',
        'description',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:3',
            'movement_type' => FeedMovementType::class,
        ];
    }

    public function feedItem(): BelongsTo
    {
        return $this->belongsTo(FeedItem::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(FeedWarehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(FeedWarehouse::class, 'to_warehouse_id');
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
