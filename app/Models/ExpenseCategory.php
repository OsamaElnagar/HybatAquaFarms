<?php

namespace App\Models;

use App\Observers\ExpenseCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ExpenseCategoryObserver::class])]
class ExpenseCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pettyCashTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }
}
