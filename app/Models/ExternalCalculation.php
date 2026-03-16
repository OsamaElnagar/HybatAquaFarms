<?php

namespace App\Models;

use App\Enums\ExternalCalculationStatementStatus;
use App\Observers\ExternalCalculationObserver;
use Database\Factories\ExternalCalculationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([ExternalCalculationObserver::class])]
class ExternalCalculation extends Model
{
    /** @use HasFactory<ExternalCalculationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ExternalCalculationEntry::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(ExternalCalculationStatement::class);
    }

    public function activeStatement(): HasOne
    {
        return $this->hasOne(ExternalCalculationStatement::class)
            ->where('status', ExternalCalculationStatementStatus::Open)
            ->latestOfMany();
    }

    public function openNewStatement(string $title, ?string $notes = null, ?float $openingBalance = null): ExternalCalculationStatement
    {
        return \DB::transaction(function () use ($title, $notes, $openingBalance) {
            $currentStatement = $this->activeStatement;

            if ($openingBalance === null) {
                $openingBalance = $currentStatement ? $currentStatement->net_balance : 0;
            }

            if ($currentStatement) {
                $currentStatement->update([
                    'status' => ExternalCalculationStatementStatus::Closed,
                    'closed_at' => now(),
                    'closing_balance' => $currentStatement->net_balance,
                ]);
            }

            return $this->statements()->create([
                'title' => $title,
                'opened_at' => now(),
                'opening_balance' => $openingBalance,
                'status' => ExternalCalculationStatementStatus::Open,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
