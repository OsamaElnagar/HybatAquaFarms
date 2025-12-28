<?php

namespace App\Observers;

use App\Enums\MovementType;
use App\Models\BatchMovement;
use App\Models\HarvestUnit;

class HarvestUnitObserver
{
    public function created(HarvestUnit $harvestUnit): void
    {
        $this->syncBatchMovement($harvestUnit);
    }

    public function updated(HarvestUnit $harvestUnit): void
    {
        $this->syncBatchMovement($harvestUnit);
    }

    public function deleted(HarvestUnit $harvestUnit): void
    {
        $this->findBatchMovement($harvestUnit)?->delete();
    }

    protected function syncBatchMovement(HarvestUnit $harvestUnit): void
    {
        // Ensure we have the harvest relation loaded
        $harvestUnit->loadMissing('harvest');
        $harvest = $harvestUnit->harvest;

        if (! $harvest) {
            return;
        }

        // If no fish harvested, delete any existing movement and return
        if (($harvestUnit->fish_count_harvested ?? 0) <= 0) {
            $this->findBatchMovement($harvestUnit)?->delete();

            return;
        }

        $notes = "Auto-generated from Harvest Unit ID: {$harvestUnit->id}";

        $data = [
            'batch_id' => $harvest->batch_id,
            'movement_type' => MovementType::Harvest,
            'from_farm_id' => $harvest->farm_id,
            'from_unit_id' => $harvestUnit->unit_id,
            'quantity' => $harvestUnit->fish_count_harvested,
            'date' => $harvest->harvest_date ?? now(),
            // 'weight' => null, // HarvestUnit doesn't track weight
            'reason' => "Harvest #{$harvest->harvest_number}",
            'notes' => $notes,
            'recorded_by' => $harvest->recorded_by ?? auth()->id(),
        ];

        $movement = $this->findBatchMovement($harvestUnit);

        if ($movement) {
            $movement->update($data);
        } else {
            BatchMovement::create($data);
        }
    }

    protected function findBatchMovement(HarvestUnit $harvestUnit): ?BatchMovement
    {
        // We use the unique notes signature to find the corresponding movement
        // This avoids modifying the schema to add a foreign key, though adding a key would be cleaner in the long run.
        return BatchMovement::query()
            ->where('movement_type', MovementType::Harvest)
            // We search for the ID specifically in the notes
            ->where('notes', 'LIKE', "%Harvest Unit ID: {$harvestUnit->id}")
            ->first();
    }
}
