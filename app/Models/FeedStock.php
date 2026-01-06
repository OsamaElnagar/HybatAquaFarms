<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedStock extends Model
{
    /** @use HasFactory<\Database\Factories\FeedStockFactory> */
    use HasFactory, Cacheable;

    protected $fillable = [
        'feed_warehouse_id',
        'feed_item_id',
        'quantity_in_stock',
        'average_cost',
        'total_value',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in_stock' => 'decimal:3',
            'average_cost' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(FeedWarehouse::class, 'feed_warehouse_id');
    }

    public function feedItem(): BelongsTo
    {
        return $this->belongsTo(FeedItem::class);
    }
}
