# Updating Batch Payments for Multiple Species / Suppliers

The project previously assumed a `Batch` has a single `factory_id` (Supplier) and `species_id`. Now it uses a `BatchFish` model to allow multiple species and factories (suppliers) per batch.

This means a `BatchPayment` (which has `batch_id` and `factory_id`) needs to be aware of the new structure.

## Proposed Changes

1. **Update `BatchPaymentForm` & `BatchPaymentsTable` in Filament**
   - In `BatchPaymentForm`, the `factory_id` selection should be constrained to factories that actually supplied fish to the selected `batch_id`. We can do this dynamically if `batch_id` is selected. Or keep it simple for now to just show `FactoryType::SEEDS` but it's better to show only the ones involved in the `BatchFish`.
   - Wait, `BatchPayment` already has `factory_id`. If a user pays a supplier for a batch, they choose the `batch_id` and the `factory_id`. This relation is actually still valid because the payment is to a *factory* (supplier) for a *batch*.
   - What needs to be updated is how the **outstanding balance** and **payment status** are calculated, because `total_cost` was previously on the `Batch` itself, but now it might be distributed across `BatchFish`. Or does `Batch` still have a `total_cost`?
   - In `BatchFishObserver`, we can see it updates `Batch->total_cost`: `$batch->updateQuietly(['total_cost' => $totalCost]);`.
   - So the `Batch` still knows its `total_cost`.
   - But what about the payment per *supplier*? Since there are multiple suppliers in a batch, does the payment status need to be tracked per supplier for the batch?

2. **Wait, let's look at the user request again:**
   "At the beginning we were implementing the every batch in a way that it will contain a single type of fish and then a single seeds supplier and as a result we ended up with this structure for the batch payment. We then moved to the batch fish way where we can put several types of species inside the same patch coming from different suppliers so we of course need to update the way batch payments works"

   If a batch has multiple suppliers, then a single payment might be to *one* of those suppliers. `BatchPayment` has a `factory_id` to indicate which supplier is being paid.
   Currently, the relation managers and forms might be assuming a single factory per batch, or they might calculate outstanding balance for the whole batch rather than per supplier.

3. **Let's check `BatchPaymentsRelationManager`:**
   In `BatchPaymentsRelationManager` (which is inside `Factory` resources), it currently forces the `batch_id` to be one where `factory_id` matches the current factory owner:
   ```php
   Select::make('batch_id')
       ->relationship('batch', 'batch_code', function ($query, $livewire) {
           return $query->where('factory_id', $livewire->ownerRecord->id);
       })
   ```
   But now, the `Batch` doesn't necessarily have `factory_id` directly (or it might be a primary one, but the real link is through `BatchFish`). So this query needs to be updated to:
   ```php
   return $query->whereHas('fish', function ($q) use ($livewire) {
       $q->where('factory_id', $livewire->ownerRecord->id);
   });
   ```

4. **Let's check `BatchPaymentForm` in `BatchPaymentsResource`:**
   ```php
   Select::make('batch_id') ...
   Select::make('factory_id')
       ->relationship('factory', 'name', function (Builder $query) {
           return $query->where('type', FactoryType::SEEDS);
       })
   ```
   Ideally, when `batch_id` is selected, `factory_id` options should be limited to the suppliers involved in that batch (via `BatchFish`).
   Or, conversely, if `factory_id` is selected, `batch_id` options should be limited to batches where this factory supplied fish.

5. **Let's check `BatchPaymentObserver`:**
   The observer posts accounting entries.
   ```php
   $factory = $payment->factory;
   $description = "... {$factory->name}";
   ```
   This looks fine, as the payment belongs to a specific factory.

6. **Let's check `BatchPaymentSummaryWidget` or similar (from previous search):**
   Need to make sure it handles the supplier cost correctly. If a batch has 2 suppliers (Supplier A: 10k, Supplier B: 5k), and a payment of 5k is made to Supplier A. The outstanding balance for the batch is 10k. But what is the outstanding balance for Supplier A?
   Maybe they need a way to track cost & payments *per supplier per batch*.
