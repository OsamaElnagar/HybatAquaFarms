<?php

namespace App\Models;

use App\Observers\FeedWarehouseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(FeedWarehouseObserver::class)]
class FeedWarehouse extends Model
{
    /** @use HasFactory<\Database\Factories\FeedWarehouseFactory> */
    use HasFactory, Cacheable;

    protected $fillable = [
        'farm_id',
        'code',
        'name',
        'location',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(FeedStock::class);
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class);
    }

    public function movementsFrom(): HasMany
    {
        return $this->hasMany(FeedMovement::class, 'from_warehouse_id');
    }

    public function movementsTo(): HasMany
    {
        return $this->hasMany(FeedMovement::class, 'to_warehouse_id');
    }
}
