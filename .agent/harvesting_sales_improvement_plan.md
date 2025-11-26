# Harvesting & Sales System - Improvement Plan

## Real-World Context Analysis

Based on the trader receipts and operational requirements, here's what's happening in reality:

### **Key Operational Facts:**

1. **Gradual Harvesting**

    - Harvesting doesn't happen all at once
    - Farm might harvest 50% of units, redistribute remaining fish to grow more
    - Harvest operations span **3-20+ days** continuously

2. **Multi-Trader Distribution**

    - Fish are classified/graded **during** the harvest
    - Different grades go to different traders
    - One harvest operation = multiple traders receiving fish

3. **Box-Level Sales Unit**

    - Each box/crate is the **actual sales unit**
    - Boxes contain different classifications: بلطي, نمرة 1, نمرة 2, نمرة 3, جامبو, خرط, etc.
    - Each box has: classification, weight (kg), fish count, unit price, total price
    - Different boxes → different traders

4. **Trader-Specific Terms**

    - Each trader has individual **commission rate** (عمولة)
    - Transport costs tracked (نقل وتعريبة)
    - Payment terms vary per trader

5. **Receipt Structure** (from images)
    - **Header**: Trader info, date, farm/batch reference
    - **Line Items** (per box/classification):
        - فراش/ترتيب: Order/line number
        - جنــيه: Species/type
        - صنــف: Classification/grade
        - كيلو: Weight in kg
        - عدد: Fish count
        - سعر: Unit price (per kg or piece)
        - المبلغ: Line total
    - **Footer**:
        - Subtotal (بوكسة/إجمالي)
        - Commission (عمولة) - percentage-based
        - Transport (نقل وتعريبة)
        - **Final Total** (الإجمـــال)

---

## Current System Gaps

### **Critical Issues:**

1. ❌ **No Harvest Operation/Session Concept**

    - Can't group multi-day harvests as one operation
    - No way to track "harvest started" vs "harvest ongoing" vs "harvest completed"

2. ❌ **Single Trader Limitation**

    - `Harvest.sales_order_id` links to ONE sales order = ONE trader only
    - Real world: one harvest → multiple traders

3. ❌ **Harvest Box Missing Sales Data**

    - No classification/grade field
    - No unit_price
    - No trader_id (who bought it)
    - No link to sales order

4. ❌ **No Commission Tracking**

    - Traders table missing `commission_rate`
    - No calculation or recording of commission amounts

5. ❌ **No Transport Cost Tracking**

    - Missing from both Trader and SalesOrder

6. ❌ **Batch Status Logic Missing**

    - No automatic determination of when batch is "depleted/harvested"
    - Need to track: which units harvested, how much remains

7. ❌ **No Unit-Level Harvest Tracking**
    - Can't tell which farm units were harvested on which days
    - Can't track partial unit harvests

---

## Proposed New Architecture

### **1. New Entity: Harvest Operations**

**Purpose**: Group related harvests over time as one logical operation

```
harvest_operations
├── operation_number (unique, e.g., HOP-001)
├── batch_id (which batch is being harvested)
├── farm_id
├── start_date (when harvest started)
├── end_date (when harvest completed, nullable)
├── status: ongoing, paused, completed
├── total_boxes_harvested (calculated)
├── total_weight_harvested (calculated)
├── total_fish_harvested (calculated)
├── estimated_duration_days
├── notes
├── created_by
└── timestamps
```

**Benefits**:

-   Track multi-day harvests as single operation
-   Calculate operation-level metrics
-   Better reporting and analytics

---

### **2. Modified Entity: Harvests**

**Changes to `harvests` table**:

```diff
harvests
├── harvest_number
+ ├── harvest_operation_id (FK to harvest_operations)
├── batch_id
├── farm_id
- ├── unit_id (REMOVE - move to harvest_units pivot)
- ├── sales_order_id (REMOVE - boxes link to orders now)
├── harvest_date (specific day of harvest)
+ ├── shift (morning/afternoon/night - optional)
- ├── boxes_count (REMOVE - calculate from boxes)
- ├── total_weight (REMOVE - calculate from boxes)
- ├── total_quantity (REMOVE - calculate from boxes)
- ├── average_weight_per_box (REMOVE - calculate)
- ├── average_fish_weight (REMOVE - calculate)
├── status: pending, in_progress, completed
├── recorded_by
├── notes
└── timestamps
```

**Reasoning**:

-   One harvest = one day's work (or shift)
-   Belongs to a harvest operation
-   Can span multiple units (pivot table)
-   All metrics calculated from boxes

---

### **3. New Pivot: Harvest Units**

**Purpose**: Track which units were harvested in each harvest session

```
harvest_units
├── id
├── harvest_id
├── unit_id (FK to farm_units)
├── fish_count_before (estimated fish in unit before)
├── fish_count_harvested (how many taken from this unit)
├── fish_count_remaining (estimated remaining)
├── percentage_harvested (calculated)
└── timestamps
```

**Benefits**:

-   Track partial unit harvests
-   Know which units were touched
-   Calculate batch depletion across units

---

### **4. Significantly Modified: Harvest Boxes**

**This becomes the PRIMARY SALES UNIT**

```diff
harvest_boxes
├── id
├── harvest_id
+ ├── harvest_operation_id (denormalized for quick queries)
+ ├── batch_id (denormalized)
+ ├── species_id (FK to species)
├── box_number (per harvest, e.g., 1, 2, 3...)
+ ├── classification (string: "بلطي", "نمرة 1", "نمرة 2", "نمرة 3", "نمرة 4", "جامبو", "خرط", etc.)
+ ├── grade (A/B/C or 1/2/3 - quality within classification)
+ ├── size_category (small/medium/large/jumbo)
├── weight (decimal 10,3)
├── fish_count
├── average_fish_weight (calculated)
+ ├── trader_id (FK to traders - who bought this box, NULLABLE until sold)
+ ├── sales_order_id (FK to sales_orders - NULLABLE until sold)
+ ├── unit_price (decimal 10,2 - price per kg or per piece)
+ ├── pricing_unit (enum: kg, piece, box)
+ ├── subtotal (calculated: weight * unit_price or fish_count * unit_price)
+ ├── is_sold (boolean, default false)
+ ├── sold_at (timestamp, nullable)
+ ├── line_number (order within invoice, nullable)
├── notes
└── timestamps

Indexes:
+ ├── (harvest_operation_id, is_sold)
+ ├── (trader_id, sales_order_id)
+ ├── (batch_id, classification)
```

**This is the 核心 change** - harvest boxes ARE the sales line items!

---

### **5. Modified: Traders Table**

```diff
traders
├── code
├── name
├── contact_person
├── phone, phone2, email
├── address
├── trader_type
├── payment_terms_days
├── credit_limit
+ ├── commission_rate (decimal 5,2 - percentage, e.g., 2.5 for 2.5%)
+ ├── commission_type (enum: percentage, fixed_per_kg, none)
+ ├── default_transport_cost_per_kg (decimal 10,2, nullable)
+ ├── default_transport_cost_flat (decimal 10,2, nullable)
├── is_active
├── notes
└── timestamps
```

---

### **6. Modified: Sales Orders Table**

```diff
sales_orders
├── order_number
├── farm_id
├── trader_id
├── date
- ├── subtotal (REMOVE - calculate from boxes)
- ├── tax_amount (keep as override, default 0)
- ├── discount_amount (keep as override, default 0)
- ├── total_amount (REMOVE - calculate)
+ ├── boxes_subtotal (calculated from harvest_boxes)
+ ├── commission_rate (decimal 5,2 - copied from trader or overridden)
+ ├── commission_amount (calculated)
+ ├── transport_cost (decimal 10,2)
+ ├── tax_amount (decimal 10,2, default 0)
+ ├── discount_amount (decimal 10,2, default 0)
+ ├── total_before_commission (calculated)
+ ├── net_amount (final amount after commission)
├── payment_status
├── delivery_status
├── delivery_date
├── delivery_address
├── created_by
├── notes
└── timestamps
```

**Calculation Flow**:

```
boxes_subtotal = SUM(harvest_boxes.subtotal WHERE sales_order_id = this.id)
commission_amount = boxes_subtotal * (commission_rate / 100)
total_before_commission = boxes_subtotal + transport_cost + tax_amount - discount_amount
net_amount = total_before_commission - commission_amount
```

---

### **7. Remove/Simplify: Sales Items**

**Option A**: **REMOVE sales_items table entirely**

-   Harvest boxes ARE the line items
-   No need for separate sales_items
-   Simpler data model

**Option B**: **Keep sales_items as "order requests"**

-   Use for pre-orders/quotes
-   Track requested vs actual delivered
-   More complex but tracks variance

**Recommendation**: **Remove sales_items** for simplicity. The harvest boxes contain everything needed.

---

## New Workflow

### **Scenario 1: Ongoing Harvest with Live Sales**

```
DAY 1:
------
1. Create Harvest Operation
   - batch_id: B-001
   - start_date: 2025-11-20
   - status: ongoing

2. Create Harvest (Day 1)
   - harvest_operation_id: HOP-001
   - harvest_date: 2025-11-20
   - shift: morning

3. Select Units Being Harvested
   - Create harvest_units records:
     * unit_id: U-01, harvested: 500 fish
     * unit_id: U-02, harvested: 300 fish

4. Record Harvest Boxes AS HARVESTED
   - Box 1: classification="بلطي", weight=50kg, count=250
   - Box 2: classification="نمرة 1", weight=45kg, count=180
   - Box 3: classification="نمرة 2", weight=40kg, count=150
   - (trader_id, sales_order_id = NULL, is_sold = false)

5. Classify and Sell Boxes to Traders

   a. Create Sales Order for Trader A
      - trader_id: T-001 (commission_rate: 2%)
      - date: 2025-11-20

   b. Assign Boxes to Sales Order
      - Update Box 1: trader_id=T-001, sales_order_id=SO-001,
                       unit_price=70 EGP/kg, pricing_unit=kg
                       subtotal = 50 * 70 = 3,500 EGP
                       is_sold=true, sold_at=now()

      - Update Box 3: trader_id=T-001, sales_order_id=SO-001,
                       unit_price=65 EGP/kg
                       subtotal = 40 * 65 = 2,600 EGP

   c. Sales Order SO-001 Calculations:
      - boxes_subtotal = 3,500 + 2,600 = 6,100 EGP
      - commission (2%) = 122 EGP
      - transport_cost = 100 EGP
      - net_amount = 6,100 + 100 - 122 = 6,078 EGP

   d. Create Sales Order for Trader B
      - Assign Box 2 to SO-002
      - Different unit_price

6. End of Day 1
   - Mark harvest (day 1) as completed
   - Harvest operation still ongoing

DAY 2:
------
7. Create Harvest (Day 2)
   - harvest_operation_id: HOP-001 (SAME operation)
   - harvest_date: 2025-11-21

8. Repeat box recording and sales...

DAY 5:
------
9. Complete Harvest Operation
   - end_date: 2025-11-24
   - status: completed

10. System Calculates:
    - Total boxes across all harvests in operation
    - Total weight, fish count
    - Check batch units depletion
```

---

### **Scenario 2: Pre-Order Fulfillment** (If keeping sales_items)

```
1. Trader places order
   - Create SalesOrder + SalesItems (requested items)

2. Start harvest operation to fulfill

3. As boxes are harvested:
   - Assign to sales order
   - Track fulfillment against sales_items

4. System shows:
   - Requested: 500kg of نمرة 1
   - Fulfilled: 480kg
   - Variance: -20kg (short)
```

---

## Batch Depletion Logic

**Auto-calculate batch status based on harvests:**

```php
// In Batch model
public function getHarvestedPercentageAttribute(): float
{
    $totalHarvested = $this->harvests()
        ->whereHas('harvestOperation', function($q) {
            $q->where('status', 'completed');
        })
        ->sum('total_quantity');

    return ($totalHarvested / $this->initial_quantity) * 100;
}

public function getAutoStatusAttribute(): BatchStatus
{
    $harvestedPct = $this->harvested_percentage;

    if ($harvestedPct >= 95) {
        return BatchStatus::Harvested; // Fully harvested
    } elseif ($harvestedPct >= 50) {
        return BatchStatus::PartiallyHarvested;
    } elseif ($this->current_quantity > 0) {
        return BatchStatus::Active;
    }

    return BatchStatus::Stocked;
}

// Track remaining fish across units
public function getRemainingQuantityAttribute(): int
{
    return $this->initial_quantity - $this->total_harvested_quantity;
}

public function getTotalHarvestedQuantityAttribute(): int
{
    return HarvestUnit::whereHas('harvest', function($q) {
        $q->where('batch_id', $this->id);
    })->sum('fish_count_harvested');
}
```

---

## Database Migration Strategy

Since you're in local development with comprehensive seeders:

### **Migration Order:**

```
1. CREATE harvest_operations table
2. CREATE harvest_units pivot table
3. DROP sales_items table (or keep and modify if preferred)
4. MODIFY harvests table (drop columns, add harvest_operation_id)
5. MODIFY harvest_boxes table (add all new fields)
6. MODIFY traders table (add commission fields)
7. MODIFY sales_orders table (add calculated fields)
8. ADD indexes and foreign keys
```

### **Seeder Updates:**

```
1. HarvestOperationSeeder - create sample operations
2. HarvestSeeder - link to operations, remove unit_id
3. HarvestBoxSeeder - add classifications, prices, traders
4. TraderSeeder - add commission rates
5. SalesOrderSeeder - calculate from boxes
6. Remove SalesItemSeeder (if removing table)
```

---

## Implementation Checklist

### **Phase 1: Database Schema**

-   [ ] Create `harvest_operations` migration
-   [ ] Create `harvest_units` pivot migration
-   [ ] Modify `harvests` migration (destructive)
-   [ ] Modify `harvest_boxes` migration (destructive)
-   [ ] Modify `traders` migration (add columns)
-   [ ] Modify `sales_orders` migration (restructure)
-   [ ] Drop `sales_items` migration (or modify if keeping)
-   [ ] Create enums: `HarvestOperationStatus`, `BoxClassification`, `PricingUnit`

### **Phase 2: Models**

-   [ ] Create `HarvestOperation` model with relationships
-   [ ] Create `HarvestUnit` pivot model
-   [ ] Update `Harvest` model (remove sales_order, add operation)
-   [ ] Update `HarvestBox` model (add all sales fields)
-   [ ] Update `Trader` model (add commission rates)
-   [ ] Update `SalesOrder` model (add calculated attributes)
-   [ ] Update `Batch` model (add depletion logic)
-   [ ] Remove `SalesItem` model (or update if keeping)

### **Phase 3: Observers & Business Logic**

-   [ ] Create `HarvestOperationObserver` (status transitions)
-   [ ] Create `HarvestBoxObserver` (calculate subtotal, update batch)
-   [ ] Update `SalesOrderObserver` (calculate from boxes, accounting)
-   [ ] Create `BatchObserver` (track depletion)

### **Phase 4: Filament Resources**

-   [ ] Create `HarvestOperationResource` (CRUD)
    -   [ ] Form schema
    -   [ ] Table
    -   [ ] Show harvests relation manager
    -   [ ] Show boxes relation manager
    -   [ ] Widgets (operation stats)
-   [ ] Update `HarvestResource`
    -   [ ] Link to operation
    -   [ ] Multi-select units (harvest_units)
    -   [ ] Remove direct sales_order link
-   [ ] Update `HarvestBoxResource`
    -   [ ] Add classification dropdown (with common options)
    -   [ ] Add trader select
    -   [ ] Add pricing fields
    -   [ ] Auto-calculate subtotal
    -   [ ] Bulk "assign to trader" action
    -   [ ] Filter by: sold/unsold, trader, classification
-   [ ] Update `SalesOrderResource`
    -   [ ] Show linked harvest boxes (relation manager)
    -   [ ] Calculate totals from boxes
    -   [ ] Show commission breakdown
    -   [ ] Transport cost field
    -   [ ] Generate invoice with box details (like receipts)
-   [ ] Update `TraderResource`
    -   [ ] Add commission rate fields
    -   [ ] Show commission earned (calculated)

### **Phase 5: Seeders**

-   [ ] Update `TraderSeeder` (add commission_rate)
-   [ ] Update `BatchSeeder` (ensure proper quantities)
-   [ ] Create `HarvestOperationSeeder`
-   [ ] Update `HarvestSeeder` (link to operations)
-   [ ] Update `HarvestBoxSeeder` (realistic classifications, prices)
-   [ ] Update `SalesOrderSeeder` (calculate from boxes)
-   [ ] Remove `SalesItemSeeder`
-   [ ] Update `DatabaseSeeder` (correct order)

### **Phase 6: Reports & Analytics**

-   [ ] Harvest operation summary report
-   [ ] Sales by classification report
-   [ ] Trader commission report
-   [ ] Batch depletion dashboard
-   [ ] Harvest efficiency metrics (kg/day, boxes/day)

### **Phase 7: Invoice/Receipt Generation**

-   [ ] Generate trader receipt (matching image format)
    -   [ ] Header: Trader, date, farm
    -   [ ] Table: Box details (classification, weight, count, price)
    -   [ ] Footer: Subtotal, commission, transport, total
-   [ ] PDF export
-   [ ] Print functionality

---

## Expected Benefits

1. ✅ **Multi-day harvest operations** tracked as single logical unit
2. ✅ **Multi-trader support** - one harvest → many traders
3. ✅ **Realistic box-level sales** with classifications
4. ✅ **Automatic commission calculations**
5. ✅ **Transport cost tracking**
6. ✅ **Batch depletion auto-calculation**
7. ✅ **Unit-level harvest tracking** (partial harvests)
8. ✅ **Accurate receipts** matching real-world format
9. ✅ **Better inventory management** (know what's sold, what's available)
10. ✅ **Flexible pricing** (per kg, per piece, per box)

---

## Questions to Clarify

1. **Sales Items**: Should we completely remove, or keep for pre-orders/variance tracking?

2. **Box Classification**: Do you want predefined list (enum) or free text? Common values from receipts:

    - بلطي (Tilapia standard)
    - نمرة 1, نمرة 2, نمرة 3, نمرة 4 (Grades 1-4)
    - جامبو (Jumbo)
    - خرط (?)
    - بلطي مين (?)

3. **Commission Calculation**:

    - Before or after transport costs?
    - Before or after discounts?
    - Current interpretation: commission on subtotal only

4. **Unit Redistribution**: When you harvest some units and redistribute fish:

    - Create BatchMovement records?
    - Auto-create new harvest_units entries?
    - How to track this flow?

5. **Pricing Unit Default**: Most common is per kg or per piece?

6. **Harvest Operation Naming**: Auto-generate like "HOP-001" or user-defined?

---

## Next Steps

Once you approve this plan, I'll:

1. Start with database migrations (destructive)
2. Update all models
3. Create seeders
4. Run `php artisan migrate:fresh --seed`
5. Build Filament resources
6. Test complete workflow

Ready when you are! 🚀
