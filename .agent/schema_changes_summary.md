# Database Schema Changes - Harvesting & Sales Restructure

## Summary of Changes

### ✅ Completed Migrations:

#### 1. **Created: harvest_operations** (New Table)

-   Tracks multi-day harvest sessions
-   Links to batch and farm
-   Status: planned, ongoing, paused, completed, cancelled
-   `operation_number` unique identifier

#### 2. **Created: harvest_units** (New Pivot Table)

-   Tracks which farm units were harvested
-   Records fish count before/after/remaining
-   Links harvest → farm_units (many-to-many)

#### 3. **Modified: harvests** ⚠️ DESTRUCTIVE

**Removed fields:**

-   `unit_id` (moved to harvest_units pivot)
-   `sales_order_id` (moved to harvest_boxes)
-   `boxes_count` (calculate from boxes)
-   `total_weight` (calculate from boxes)
-   `total_quantity` (calculate from boxes)
-   `average_weight_per_box` (calculate from boxes)
-   `average_fish_weight` (calculate from boxes)

**Added fields:**

-   `harvest_operation_id` (FK to harvest_operations)
-   `shift` (morning/afternoon/night)

**Changed:**

-   Status values: pending → in_progress → completed (removed 'sold')

#### 4. **Modified: harvest_boxes** ⚠️ EXTENSIVE CHANGES

**Added fields - Now the PRIMARY SALES UNIT:**

-   `harvest_operation_id` (denormalized)
-   `batch_id` (denormalized)
-   `species_id` (FK)
-   `classification` (بلطي, نمرة 1, نمرة 2, جامبو, etc.)
-   `grade` (A/B/C or 1/2/3)
-   `size_category` (small/medium/large/jumbo)
-   `trader_id` (FK - who bought this box)
-   `sales_order_id` (FK - which order)
-   `unit_price` (price per kg/piece/box)
-   `pricing_unit` (kg/piece/box enum)
-   `subtotal` (calculated total for box)
-   `is_sold` (boolean)
-   `sold_at` (timestamp)
-   `line_number` (position in invoice)

#### 5. **Modified: traders**

**Added fields:**

-   `commission_rate` (decimal 5,2)
-   `commission_type` (enum: percentage, fixed_per_kg, none)
-   `default_transport_cost_per_kg`
-   `default_transport_cost_flat`

#### 6. **Modified: sales_orders** ⚠️ STRUCTURAL CHANGE

**Removed fields:**

-   `subtotal` (replaced by boxes_subtotal)
-   `total_amount` (replaced by net_amount)

**Added fields:**

-   `boxes_subtotal` (sum of harvest_boxes.subtotal)
-   `commission_rate` (from trader or override)
-   `commission_amount` (calculated)
-   `transport_cost` (النقل والتعريبة)
-   `total_before_commission` (intermediate calculation)
-   `net_amount` (final amount after commission)

**Calculation Flow:**

```
boxes_subtotal = SUM(harvest_boxes.subtotal WHERE sales_order_id = X)
total_before_commission = boxes_subtotal + transport_cost + tax_amount - discount_amount
commission_amount = boxes_subtotal * (commission_rate / 100)
net_amount = total_before_commission - commission_amount
```

#### 7. **Disabled: sales_items**

-   Table creation commented out
-   harvest_boxes now serves this purpose
-   Migration file kept for reference

---

## New Enums Created:

1. **HarvestOperationStatus**: planned, ongoing, paused, completed, cancelled
2. **PricingUnit**: kg, piece, box
3. **CommissionType**: percentage, fixed_per_kg, none

---

## Next Steps:

### 1. Create Models

-   [ ] HarvestOperation
-   [ ] HarvestUnit (pivot)
-   [ ] Update Harvest
-   [ ] Update HarvestBox
-   [ ] Update Trader
-   [ ] Update SalesOrder
-   [ ] Remove/Archive SalesItem

### 2. Update Existing Seeders

-   [ ] TraderSeeder (add commission_rate)
-   [ ] Update HarvestSeeder
-   [ ] Update HarvestBoxSeeder
-   [ ] Update SalesOrderSeeder

### 3. Create New Seeders

-   [ ] HarvestOperationSeeder
-   [ ] HarvestUnitSeeder

### 4. Run Migration

```bash
php artisan migrate:fresh --seed
```

---

## Migration Dependencies (Order matters):

```
1. farms
2. batches
3. farm_units
4. species
5. traders (modified)
6. harvest_operations (new) ← must come before harvests
7. harvests (modified)
8. harvest_units (new) ← must come after harvests
9. sales_orders (modified)
10. harvest_boxes (modified)
11. sales_items (disabled)
```

All migration files have been updated to respect this order based on their timestamps.
