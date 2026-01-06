<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ElipZis\Cacheable\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Box extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'name',
        'species_id',
        'max_weight',
        'class_total_weight',
        'class',
        'category',
    ];

    protected $casts = [
        'max_weight' => 'decimal:2',
        'class_total_weight' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to filter by species
     */
    public function scopeBySpecies($query, $speciesId)
    {
        return $query->where('species_id', $speciesId);
    }

    /**
     * Scope to filter by class
     */
    public function scopeByClass($query, $class)
    {
        return $query->where('class', $class);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get full box description
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];

        if ($this->class) {
            $parts[] = $this->class;
        }

        if ($this->category) {
            $parts[] = $this->category;
        }

        return implode(' - ', $parts);
    }
}
