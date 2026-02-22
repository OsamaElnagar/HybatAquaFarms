# Enhancing Batch Relationships and Closure Logic

Based on the investigation of the application, here is the current state and the proposed plan to fulfill your vision.

## Current State Analysis
1. **The Infrastructure Exists**: The [Batch](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/Batch.php#17-383) model already has an `is_cycle_closed` flag, a `closure_date`, and columns for snapshotting costs/revenues (`total_feed_cost`, `total_revenue`, etc.).
2. **Relations Exist**: Most operational models ([DailyFeedIssue](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/DailyFeedIssue.php#11-88), [PettyCashTransaction](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashTransaction.php#12-76), `HarvestOperation`, etc.) already have a `batch_id` column.
3. **Partial Protections**: [DailyFeedIssue](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/DailyFeedIssue.php#11-88) already throws an exception if you try to add or edit an issue for a closed batch.
4. **Missing UX/Automation**:
   - There is no dedicated **Action** to safely close a batch. Modifying the `is_cycle_closed` checkbox manually is error-prone because the snapshot values (like `total_feed_cost`) need to be calculated and saved at the exact moment of closure.
   - When users create a new transaction (like Petty Cash or Feed Issue), the `batch_id` isn't always automatically inferred from the active batch of the selected `farm_id`.
   - Other models don't have the same strict "cannot edit if batch is closed" backend protections as [DailyFeedIssue](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/DailyFeedIssue.php#11-88).

## Proposed Changes

### 1. Implement a Solid "Close Batch" Action
We need an explicit action to handle everything related to closing a batch cleanly.

#### [NEW] `app/Filament/Actions/CloseBatchAction.php`
- Create a dedicated Filament action (`CloseBatchAction`) that can be used on the [BatchesTable](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Batches/Tables/BatchesTable.php#15-322), `ViewBatch`, and [EditBatch](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Batches/Pages/EditBatch.php#10-49) pages.
- The action will open a confirmation modal requiring `closure_notes` (optional).
- Upon confirmation, it will:
  - Set `is_cycle_closed` = true.
  - Set `closure_date` = today.
  - Snapshot dynamic costs: `total_feed_cost = $batch->total_feed_cost`, `total_operating_expenses = $batch->allocated_expenses`, `total_revenue = $batch->total_revenue`, `net_profit = $batch->net_profit`.
  - Set `closed_by` = current user.

#### [MODIFY] [app/Filament/Resources/Batches/Tables/BatchesTable.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Batches/Tables/BatchesTable.php) & Pages
- Add the `CloseBatchAction` to the table rows and page headers so users can explicitly click "إقفال الدورة".

### 2. Auto-Assign "Active Batch" on Transactions
We want the system to automatically know what batch a transaction belongs to when a farm is selected.

#### [MODIFY] [app/Models/Farm.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/Farm.php)
- Add a helper relationship or attribute: `activeBatch()` which returns the current open batch (where `is_cycle_closed` is false).

#### [MODIFY] Operational Filament Forms
- Update the forms for [PettyCashTransaction](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashTransaction.php#12-76), [DailyFeedIssue](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/DailyFeedIssue.php#11-88), `HarvestOperation`, `Voucher`, etc.
- When `farm_id` is selected (`->live()`), we will use `->afterStateUpdated()` to automatically fetch the farm's active batch and set the `batch_id` field behind the scenes or explicitly in the dropdown. This enforces that transactions automatically link to the open batch.

### 3. Enforce Data Integrity on Closed Batches
Ensure no one can modify historical data belonging to a closed batch.

#### [MODIFY] Model Observers / Booted Methods
- Add the same `creating`, `updating`, and `deleting` protection hooks (currently found in [DailyFeedIssue](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/DailyFeedIssue.php#11-88)) to other models like [PettyCashTransaction](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashTransaction.php#12-76), `Voucher`, `HarvestOperation`, and `BatchMovement`. If the linked batch `is_cycle_closed`, it will throw a strict Exception.

## Verification Plan
### Automated Tests
- Run Pest feature tests where applicable using `pest-testing` skill, especially for the active batch retrieval logic and the closure action logic.

### Manual Verification
- Go to the Filament UI for Batches and verify the "Close Cycle" action calculates and saves snapshot fields correctly.
- Go to "Petty Cash", select a Farm, and verify `batch_id` is automatically populated with the open batch.
- Try to edit a transaction linked to a closed batch and verify it fails, protecting historical data.
