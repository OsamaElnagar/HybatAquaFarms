<?php

namespace App\Observers;

use App\Models\FeedItem;

class FeedItemObserver
{
    public function creating(FeedItem $feedItem): void
    {
        if (! $feedItem->code) {
            $feedItem->code = static::generateCode();
        }
    }

    protected static function generateCode(): string
    {
        $lastFeedItem = FeedItem::latest('id')->first();
        $number = $lastFeedItem ? ((int) substr($lastFeedItem->code, 5)) + 1 : 1;

        return 'FEED-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
