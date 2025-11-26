# Phase 1 Complete: Database & Models ✅

## What We've Built:

### 📊 **Enums Created (3)**

✅ `HarvestOperationStatus` - planned, ongoing, paused, completed, cancelled
✅ `PricingUnit` - kg, piece, box
✅ `CommissionType` - percentage, fixed_per_kg, none

### 🗄️ **Migrations Created/Modified (7)**

#### New Tables:

✅ `harvest_operations` - Multi-day harvest tracking
✅ `harvest_units` - Pivot: which units were harvested

#### Modified Tables:

✅ `harvests` - Linked to operations, removed calculated fields
✅ `harvest_boxes` - **NOW THE PRIMARY SALES UNIT** with full sales data
✅ `traders` - Added commission & transport fields
✅ `sales_orders` - Restructured for box-based calculations
✅ `sales_items` - Disabled (commented out)

### 🏗️ **Models Created/Updated (7)**

#### New Models:

✅ `HarvestOperation` - With auto-numbering, metrics
✅ `HarvestUnit` - Pivot with auto-calculations

#### Updated Models:

✅ `Harvest` - Works with operations, calculates from boxes  
✅ `HarvestBox` - **SUPERCHARGED** with sales functionality
✅ `Trader` - Commission & transport fields
✅ `SalesOrder` - Calculates from boxes, commission logic
✅ `Batch` - (inherited harvest relationships)

---

## 🎯 New Data Flow:

```
HarvestOperation (HOP-001)
  ├── Harvest (Day 1) → H-00001
  │     ├── HarvestUnit (Unit 1: 500 fish)
  │     ├── HarvestUnit (Unit 2: 300 fish)
  │     └── HarvestBoxes
  │           ├── Box 1: بلطي, 50kg, 250 fish
  │           ├── Box 2: نمرة 1, 45kg, 180 fish
  │           └── Box 3: نمرة 2, 40kg, 150 fish
  ├── Harvest (Day 2) → H-00002
  │     └── HarvestBoxes...
  └── Harvest (Day 3) → H-00003
        └── HarvestBoxes...

Trader A (Commission: 2%)
  └── SalesOrder SO-00001
        ├── HarvestBox #1 (from HOP-001)
        ├── HarvestBox #3 (from HOP-001)
        └── Calculations:
              boxes_subtotal = 6,100 EGP
              commission (2%) = 122 EGP
              transport = 100 EGP
              net_amount = 6,078 EGP
```

---

## 🔑 Key Features Implemented:

### **HarvestOperation Model:**

-   Auto-generates `operation_number` (HOP-0001, HOP-0002...)
-   Tracks status lifecycle
-   Calculates totals from all harvests/boxes
-   Links to batch, farm
-   Shows sold vs unsold boxes

### **Harvest Model:**

-   Auto-generates `harvest_number` (H-00001, H-00002...)
-   Links to parent operation
-   Many-to-many with farm units (via harvest_units)
-   Calculates all metrics from boxes (weight, count, averages)
-   Cycle closure protection

### **HarvestBox Model - THE STAR ⭐:**

-   Full sales entity with classification, pricing
-   Auto-calculates average fish weight
-   Auto-calculates subtotal based on pricing unit
-   Methods: `assignToSalesOrder()`, `unassignFromSalesOrder()`
-   When saved, triggers parent SalesOrder recalculation
-   Display name from classification/species/grade

### **SalesOrder Model:**

-   Auto-generates `order_number` (SO-00001, SO-00002...)
-   Copies commission rate from trader
-   `recalculateTotals()` - sums all linked harvest boxes
-   Commission calculation logic
-   Links to harvest boxes (not sales items!)
-   Can see source harvest operations

### **Trader Model:**

-   Commission rate & type
-   Default transport costs (per kg or flat)

---

## 📝 Next Steps:

### Phase 2: Seeders & Data

-   [ ] Update `DatabaseSeeder` call order
-   [ ] Update `TraderSeeder` (add commission_rate data)
-   [ ] Create `HarvestOperationSeeder`
-   [ ] Create `HarvestUnitSeeder`
-   [ ] Update `HarvestSeeder` (link to operations)
-   [ ] Update `HarvestBoxSeeder` (add classifications, sales data)
-   [ ] Update `SalesOrderSeeder` (remove sales_items, work with boxes)
-   [ ] Remove/comment `SalesItemSeeder`

### Phase 3: Test Migration

```bash
php artisan migrate:fresh --seed
```

### Phase 4: Filament Resources

-   [ ] Create `HarvestOperationResource`
-   [ ] Update `HarvestResource`
-   [ ] Update `HarvestBoxResource`
-   [ ] Update `SalesOrderResource`
-   [ ] Update `TraderResource`

### Phase 5: Relation Managers

-   [ ] HarvestOperation → Harvests
-   [ ] HarvestOperation → HarvestBoxes
-   [ ] HarvestOperation → SalesOrders
-   [ ] SalesOrder → HarvestBoxes
-   [ ] Harvest → HarvestUnits

---

## 🚨 Important Notes:

1. **sales_items table is disabled** - harvest_boxes replaces it
2. **SalesItem model will be removed** after testing
3. **Destructive migrations** - running fresh will lose data (OK for dev)
4. **Auto-numbering** implemented for: HarvestOperation, Harvest, SalesOrder
5. **Auto-calculations** in HarvestBox observer
6. **Commission from trader** auto-copied to sales order

---

## 🎨 Workflow in UI (Coming):

```
User opens: Harvest Operations
  → Creates new operation (selects batch)
  → Operation starts (status: ongoing)

  → Creates Harvest (Day 1)
    → Selects units being harvested
    → Records boxes with classifications

  → Creates Harvest (Day 2)
    → More boxes...

  → Views unsold boxes
    → Bulk assigns to Trader A
    → Creates Sales Order
    → Boxes auto-priced
    → Commission calculated

  → Completes operation
    → Status: completed
    → View full metrics
```

---

Ready for Phase 2: Seeders! 🌱
