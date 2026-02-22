<?php

use App\Models\DailyFeedIssue;
use App\Models\Farm;
use App\Models\FeedItem;
use App\Models\FeedWarehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('daily feed issue creation caches feed item and quantity', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $warehouse = FeedWarehouse::factory()->create(['farm_id' => $farm->id, 'name' => 'Main Warehouse']);
    $feedItem = FeedItem::factory()->create();

    // Create stock so movement doesnt fail
    \App\Models\FeedStock::factory()->create([
        'feed_warehouse_id' => $warehouse->id,
        'feed_item_id' => $feedItem->id,
        'quantity_in_stock' => 100,
    ]);

    $this->actingAs($user);

    $issue = DailyFeedIssue::factory()->create([
        'feed_warehouse_id' => $warehouse->id,
        'feed_item_id' => $feedItem->id,
        'quantity' => 50,
        'recorded_by' => $user->id,
    ]);

    expect(Cache::get('user_'.$user->id.'_last_feed_item'))->toBe($feedItem->id)
        ->and(Cache::get('user_'.$user->id.'_last_feed_qty'))->toEqual(50);
});
