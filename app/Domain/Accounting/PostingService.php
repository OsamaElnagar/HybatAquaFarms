<?php

namespace App\Domain\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PostingRule;
use Illuminate\Support\Facades\DB;

class PostingService
{
    public function post(string $eventKey, array $context = []): JournalEntry
    {
        $rule = PostingRule::query()->where('event_key', $eventKey)->where('is_active', true)->firstOrFail();

        return DB::transaction(function () use ($rule, $context) {
            $entry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber(),
                'date' => $context['date'] ?? now()->toDateString(),
                'description' => $context['description'] ?? $rule->description,
                'source_type' => $context['source_type'] ?? null,
                'source_id' => $context['source_id'] ?? null,
                'is_posted' => true,
                'posted_by' => $context['user_id'] ?? null,
                'posted_at' => now(),
            ]);

            $amount = (float) ($context['amount'] ?? 0);
            $farmId = $context['farm_id'] ?? null;

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $rule->debit_account_id,
                'farm_id' => $farmId,
                'debit' => $amount,
                'credit' => 0,
                'description' => $context['debit_description'] ?? null,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $rule->credit_account_id,
                'farm_id' => $farmId,
                'debit' => 0,
                'credit' => $amount,
                'description' => $context['credit_description'] ?? null,
            ]);

            return $entry;
        });
    }

    protected function generateEntryNumber(): string
    {
        $prefix = now()->format('Ymd');
        $seq = str_pad((string) (JournalEntry::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "JE-{$prefix}-{$seq}";
    }
}
