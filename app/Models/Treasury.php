<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treasury extends Model
{
    protected $table = null; // Aggregate, no table

    public static function getCashBalance($farmId = null)
    {
        $query = Account::cash();
        if ($farmId) $query->where('farm_id', $farmId);
        return $query->sum('balance');
    }

    public static function getPettyBalance($farmId = null)
    {
        $query = PettyCash::withSum(['transactions as total_in' => fn($q) => $q->in()], 'amount')
                          ->withSum(['transactions as total_out' => fn($q) => $q->out()], 'amount');
        if ($farmId) $query->where('farm_id', $farmId);
        return $query->get()->sum(fn($pc) => $pc->opening_balance + $pc->total_in - $pc->total_out + $pc->account?->balance ?? 0);
    }

    public static function getTotal($farmId = null)
    {
        return static::getCashBalance($farmId) + static::getPettyBalance($farmId);
    }

    public static function recentCashLinesQuery($limit = 50, $farmId = null)
    {
        $query = JournalLine::with(['journalEntry.source', 'account'])
            ->whereHas('account', fn($q) => $q->cash())
            ->latest('created_at');
        if ($farmId) $query->where('farm_id', $farmId);
        return $query->limit($limit);
    }
}
