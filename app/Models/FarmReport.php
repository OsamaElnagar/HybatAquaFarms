<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmReport extends Model
{
    protected $fillable = [
        'farm_id',
        'user_id',
        'filters',
        'total_expenses',
        'total_revenue',
        'net_profit',
        'profit_margin',
        'batch_count',
        'extra_expenses',
        'extra_revenue',
        'other_transactions',
        'pdf_path',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'total_expenses' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'extra_expenses' => 'decimal:2',
            'extra_revenue' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'profit_margin' => 'decimal:2',
            'other_transactions' => 'array',
        ];
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
