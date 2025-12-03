<?php

namespace App\Observers;

use App\Models\JournalEntry;

class JournalEntryObserver
{
    public function creating(JournalEntry $entry): void
    {
        if (! $entry->entry_number) {
            $entry->entry_number = static::generateEntryNumber();
        }
    }

    protected static function generateEntryNumber(): string
    {
        $lastEntry = JournalEntry::latest('id')->first();
        $number = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-'.str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
