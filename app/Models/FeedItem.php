<?php

namespace App\Models;

use App\Observers\FeedItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([FeedItemObserver::class])]
class FeedItem extends Model
{
    /** @use HasFactory<\Database\Factories\FeedItemFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'factory_id',
        'description',
        'unit_of_measure',
        'standard_cost',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'standard_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(FeedStock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(FeedMovement::class);
    }

    public function dailyFeedIssues(): HasMany
    {
        return $this->hasMany(DailyFeedIssue::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }
}
