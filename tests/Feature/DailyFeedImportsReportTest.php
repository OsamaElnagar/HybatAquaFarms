<?php

declare(strict_types=1);

use App\Enums\FeedMovementType;
use App\Enums\UserType;
use App\Filament\Pages\DailyFeedImportsReport;
use App\Filament\Widgets\DailyFeedImportsChart;
use App\Models\Factory;
use App\Models\FeedItem;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create([
        'user_type' => UserType::Owner,
    ]);
    actingAs($this->user);
});

it('renders daily feed imports report page', function () {
    get(DailyFeedImportsReport::getUrl())
        ->assertSuccessful();
})->todo();

it('renders daily feed imports chart widget', function () {
    Livewire::test(DailyFeedImportsChart::class)
        ->assertSuccessful();
});

it('shows chart with feed movement data', function () {
    $factory = Factory::factory()->create(['is_active' => true]);
    $feedItem = FeedItem::factory()->create(['is_active' => true]);
    $warehouse = FeedWarehouse::factory()->create(['is_active' => true]);

    FeedMovement::factory()->create([
        'movement_type' => FeedMovementType::In,
        'factory_id' => $factory->id,
        'feed_item_id' => $feedItem->id,
        'to_warehouse_id' => $warehouse->id,
        'date' => now(),
        'quantity' => 100.5,
    ]);

    FeedMovement::factory()->create([
        'movement_type' => FeedMovementType::In,
        'factory_id' => $factory->id,
        'feed_item_id' => $feedItem->id,
        'to_warehouse_id' => $warehouse->id,
        'date' => now()->subDays(1),
        'quantity' => 200.0,
    ]);

    Livewire::test(DailyFeedImportsChart::class)
        ->assertSuccessful();
})->todo();

it('filters chart data by factory', function () {
    $factory1 = Factory::factory()->create(['is_active' => true, 'name' => 'Factory 1']);
    // ... setup
    Livewire::test(DailyFeedImportsChart::class)
        ->set('filters.factory_id', $factory1->id)
        ->assertSuccessful();
})->todo();

it('filters chart data by date range', function () {
    // ... setup
    Livewire::test(DailyFeedImportsChart::class)
        ->set('filters.date_start', now()->subDays(7)->format('Y-m-d'))
        ->set('filters.date_end', now()->format('Y-m-d'))
        ->assertSuccessful();
})->todo();
