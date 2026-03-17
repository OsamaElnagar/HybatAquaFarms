# Petty Cash Transaction Form: Architecture & Context-Aware Logic

This document outlines the advanced patterns and tricks implemented in the `PettyCashTransactionForm` schema to handle multiple entry points (Standalone, Relation Managers, and Bulk Actions) with a high level of complexity and automation.

## 🌟 Key Architecture Patterns

### 1. Dynamic Context Awareness
The form automatically detects its environment at the start of the `configure()` method. By analyzing the Livewire component and its `ownerRecord`, it establishes whether it's being accessed from a **Petty Cash** context or a **Farm** context.

```php
$livewire = $schema->getLivewire(); // Safely extracted
$ownerRecord = ($livewire instanceof RelationManager) ? $livewire->getOwnerRecord() : null;
$isPettyCashManager = $ownerRecord instanceof PettyCash;
$isFarmManager = $ownerRecord instanceof Farm;
```

### 2. Intelligent Defaults & Field Locking
Instead of generic defaults, the form uses context-specific logic to pre-fill fields and "lock" them (disable) when the value is implied by the navigation path.
- **Petty Cash Relation Manager**: Auto-fills the `petty_cash_id` and disables the field.
- **Farm Relation Manager**: Auto-fills the `farm_id` and disables the field.
- **Single-Link Auto-Fill**: If a Farm only has one associated Petty Cash, it automatically selects it even on a standalone page.

### 3. Integrated Balance & Logic Synchronization (`updateDependentFields`)
A specialized `updateDependentFields` helper manages the relationship between Petty Cash and current balances.
- **Validation on Change**: If a user manually changes the Petty Cash, the form validates if the currently selected Farm is still valid for that cash box. If not, it clears the farm and batch selections to prevent data inconsistency.
- **Balance Updates**: Automatically fetches and formats the current balance whenever the Petty Cash selection changes or is hydrated.

### 4. Smart Batch Selection (`selectMainBatchForFarm`)
To reduce manual clicks, a dedicated helper automatically identifies the "Main" active cycle for a farm.
- If a farm has exactly one active Main batch, it is automatically selected when the Farm field is populated.
- This logic is triggered during hydration (page load), manual interaction (change event), and cascading updates (when a Petty Cash auto-selects a Farm).

### 5. Multi-Layer Filtered Options
The `options()` closures are context-aware to improve UX:
- **Filtered Petty Cash**: When in a Farm context, the Petty Cash list is filtered to show only those linked to the current farm.
- **Optimized Performance**: Large lists (like All Petty Cashes) are cached for 24 hours to ensure snappy form rendering.

### 6. Resilience in Bulk Actions
To handle the complexity of `Repeater` components inside Actions, the schema is explicitly bound to the parent Livewire component.
- **TypeError Protection**: The `configure()` method includes a failsafe for uninitialized schemas, preventing crashes during dynamic build cycles.
- **Repeater Parity**: Every row in a Bulk Transaction repeater benefits from the same level of intelligence and validation as the main standalone form.

---

## 🚀 Pro Tips for Scaling these Patterns

1.  **Dehydrated State**: When disabling fields (`disabled()`), always use `dehydrated()` to ensure the value still submits with the form, maintaining the "Owner" link.
2.  **Helper Modularity**: By moving batch and balance logic into `private static` methods, we ensure that auto-selection logic is consistent across all triggers (interaction vs load).
3.  **Filtered Selects**: Always filter sub-selects based on parent selection using `options(fn(Get $get) => ...)` to maintain data integrity before it even hits validation.
