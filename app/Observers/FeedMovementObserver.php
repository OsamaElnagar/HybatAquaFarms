<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\FeedMovementType;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;

class FeedMovementObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(FeedMovement $movement): void
    {
        $warehouse = $movement->toWarehouse ?? $movement->fromWarehouse;
        $farmId = $warehouse instanceof FeedWarehouse ? $warehouse->farm_id : null;

        if ($movement->movement_type === FeedMovementType::In) {
            $this->posting->post('feed.purchase', [
                'amount' => (float) ($movement->total_cost ?? 0),
                'farm_id' => $farmId,
                'date' => $movement->date?->toDateString(),
                'source_type' => $movement->getMorphClass(),
                'source_id' => $movement->id,
                'description' => $movement->description,
            ]);
        } elseif ($movement->movement_type === FeedMovementType::Out) {
            $this->posting->post('feed.issue', [
                'amount' => (float) ($movement->total_cost ?? 0),
                'farm_id' => $farmId,
                'date' => $movement->date?->toDateString(),
                'source_type' => $movement->getMorphClass(),
                'source_id' => $movement->id,
                'description' => $movement->description,
            ]);
        }
    }
}
