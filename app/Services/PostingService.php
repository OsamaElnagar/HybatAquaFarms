<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PostingRule;
use Illuminate\Support\Facades\DB;

class PostingService
{
    public static function post($source, string $eventKey, float $amount): JournalEntry
    {
        return DB::transaction(function () use ($source, $eventKey, $amount) {
            $rule = PostingRule::where('event_key', $eventKey)
                               ->where('is_active', true)
                               ->firstOrFail();

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(), // Assume method or trait
                'date' => now(),
                'description' => $rule->description . ' for ' . class_basename($source),
                'source_type' => get_class($source),
                'source_id' => $source->id,
                'is_posted' => true,
                'posted_by' => auth()->id() ?? 1, // Or current user
                'posted_at' => now(),
            ]);

            // Debit line
            if ($rule->debit_account_id) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $rule->debit_account_id,
                    'farm_id' => $source->farm_id ?? null,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => $rule->description . ' (debit)',
                ]);
            }

            // Credit line
            if ($rule->credit_account_id) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $rule->credit_account_id,
                    'farm_id' => $source->farm_id ?? null,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => $rule->description . ' (credit)',
                ]);
            }

            return $entry;
        });
    }
}
