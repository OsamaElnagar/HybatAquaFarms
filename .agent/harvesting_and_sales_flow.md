# Harvesting and Sales Flow - HybatAquaFarms

## Overview

This document explains the implemented flow for harvesting fish and linking it to sales orders in the HybatAquaFarms system.

---

## Database Schema

### 1. **Harvests Table**

-   **Purpose**: Record individual harvest operations from fish batches
-   **Key Fields**:
    -   `harvest_number` - Unique identifier
    -   `batch_id` - Foreign key to batches (the source of harvested fish)
    -   `farm_id` - Farm where harvest occurred
    -   `unit_id` - Specific farm unit (pond/cage)
    -   `sales_order_id` - **Optional** link to sales order
    -   `harvest_date` - When harvest occurred
    -   `boxes_count` - Number of boxes/cages used
    -   `total_weight` - Total weight in kg
    -   `total_quantity` - Total fish count
    -   `average_fish_weight` - Average weight per fish (grams)
    -   `status` - Enum: `pending`, `completed`, `sold`
    -   `recorded_by` - User who recorded the harvest

### 2. **Harvest Boxes Table**

-   **Purpose**: Detailed breakdown of individual boxes in a harvest
-   **Key Fields**:
    -   `harvest_id` - Parent harvest
    -   `box_number` - Box identifier
    -   `weight` - Box weight (kg)
    -   `fish_count` - Fish in this box
    -   `average_fish_weight` - Average per fish in grams

### 3. **Sales Orders Table**

-   **Purpose**: Customer orders for fish
-   **Key Fields**:
    -   `order_number` - Unique identifier
    -   `farm_id` - Source farm
    -   `trader_id` - Customer/trader
    -   `date` - Order date
    -   `subtotal`, `tax_amount`, `discount_amount`, `total_amount` - Financial fields
    -   `payment_status` - Enum: `pending`, `partial`, `paid`
    -   `delivery_status` - Enum: `pending`, `in_transit`, `delivered`
    -   `delivery_date`, `delivery_address` - Delivery info

### 4. **Sales Items Table**

-   **Purpose**: Line items in sales orders (what was ordered)
-   **Key Fields**:
    -   `sales_order_id` - Parent order
    -   `batch_id` - **Optional** specific batch to fulfill from
    -   `species_id` - Type of fish
    -   `quantity` - Ordered quantity (pieces)
    -   `weight_kg` - Ordered weight
    -   `unit_price` - Price per unit
    -   `pricing_unit` - `kg` or `piece`
    -   `fulfilled_quantity` - How much has been fulfilled
    -   `fulfilled_weight` - Weight fulfilled
    -   `fulfillment_status` - Enum: `pending`, `partial`, `fulfilled`

---

## Entity Relationships

```
Farm
  └── Batches (HasMany)
        ├── Harvests (HasMany)
        │     ├── HarvestBoxes (HasMany)
        │     └── SalesOrder (BelongsTo - Optional)
        └── SalesItems (HasMany)

SalesOrder
  ├── SalesItems (HasMany)
  ├── Harvests (HasMany)
  └── Trader (BelongsTo)
```

---

## Flow Implementation

### **Scenario 1: Harvest WITHOUT Pre-existing Sales Order**

This is the typical harvest-first approach where you harvest fish and later sell them.

1. **Create Harvest**

    - User records a harvest from a specific `Batch`
    - Records: date, boxes, total weight, fish count
    - System calculates average fish weight automatically
    - `sales_order_id` is **NULL**
    - Status starts as `pending`

2. **Record Harvest Boxes** (Optional Detail)

    - For each box, record individual weights and counts
    - System can calculate box-level averages

3. **Harvest Completion**

    - Status changes to `completed`
    - Fish is now available for sale

4. **Create Sales Order Later**

    - User creates a sales order for a trader
    - Adds sales items specifying species, quantity, weight
    - Can optionally specify which `batch_id` to source from

5. **Link Harvest to Sales**
    - User can update the harvest record
    - Set `sales_order_id` to link it to the sale
    - Status changes to `sold`

---

### **Scenario 2: Sales Order FIRST, Then Harvest to Fulfill**

This is the order-fulfillment approach where you have a customer order first.

1. **Create Sales Order**

    - User creates an order for a trader
    - Adds sales items with:
        - Species, quantity, weight requirements
        - Optionally specify `batch_id` to source from
    - `fulfillment_status` starts as `pending`

2. **View Sales Order**

    - On the sales order page, there's a **Harvests Relation Manager**
    - Shows all harvests linked to this order

3. **Create Harvest to Fulfill Order**

    - User creates a harvest from the required batch
    - **Sets `sales_order_id`** during creation to link it immediately
    - Records boxes, weights, quantities

4. **Automatic Fulfillment Tracking**

    - When harvest is linked:
        - System can track `fulfilled_quantity` and `fulfilled_weight` in sales items
        - Update `fulfillment_status`: `pending` → `partial` → `fulfilled`

5. **Order Completion**
    - When all items are fulfilled, order status updates
    - Delivery status can be managed separately

---

## Key Business Logic

### **Harvest Model** (`app/Models/Harvest.php`)

**Cycle Closure Protection**:

```php
static::creating/updating/deleting(function ($model) {
    if ($model->batch && $model->batch->is_cycle_closed) {
        throw new Exception("لا يمكن التعديل - الدورة مقفلة");
    }
});
```

-   Once a batch cycle is closed, no harvest changes allowed
-   Ensures data integrity for historical records

**Relationships**:

-   `batch()` - Source batch
-   `salesOrder()` - Optional linked sales order
-   `boxes()` - Detailed box breakdown
-   `farm()`, `unit()` - Location info
-   `recordedBy()` - Audit trail

---

### **Sales Item Model** (`app/Models/SalesItem.php`)

**Automatic Pricing Calculation**:

```php
static::creating/updating(function ($item) {
    $item->calculatePricing();
});

public function calculatePricing(): void {
    // Calculate based on pricing_unit (kg or piece)
    if ($this->pricing_unit === 'piece') {
        $this->subtotal = $this->quantity * $this->unit_price;
    } else {
        $this->subtotal = $this->weight_kg * $this->unit_price;
    }

    // Apply discounts
    // Calculate average fish weight
}
```

**Fulfillment Tracking Attributes**:

-   `fulfillment_progress` - Percentage completed
-   `remaining_quantity` - What's left to fulfill
-   `remaining_weight` - Weight left to fulfill
-   `is_fully_fulfilled` - Boolean check

**Harvest Relationship** (Complex):

```php
public function harvests(): HasMany {
    return $this->hasMany(Harvest::class, 'sales_order_id', 'sales_order_id')
        ->where('batch_id', $this->batch_id);
}
```

Links to harvests from the same batch and sales order.

---

### **Sales Order Model** (`app/Models/SalesOrder.php`)

**Relationships**:

-   `items()` - Line items in the order
-   `harvests()` - All harvests linked to this order
-   `trader()` - Customer
-   `journalEntries()` - Accounting integration

**Computed Attributes**:

-   `total_items` - Count of line items
-   `total_quantity` - Sum of all quantities ordered

---

### **Sales Order Observer** (`app/Observers/SalesOrderObserver.php`)

**Automatic Accounting Integration**:

```php
public function created(SalesOrder $salesOrder): void {
    // Post to accounting based on payment status
    $eventKey = $salesOrder->payment_status === PaymentStatus::Paid
        ? 'sales.cash'   // Debit: Cash, Credit: Sales Revenue
        : 'sales.credit'; // Debit: Accounts Receivable, Credit: Sales Revenue

    $this->posting->post($eventKey, [...]);
}

public function updated(SalesOrder $salesOrder): void {
    // When payment status changes to Paid
    if ($salesOrder->wasChanged('payment_status')) {
        // Post payment receipt
        // Debit: Cash, Credit: Accounts Receivable
    }
}
```

---

## Harvest Status Flow

```
┌─────────┐
│ Pending │ ← Initial state when harvest is created
└────┬────┘
     │
     ▼
┌───────────┐
│ Completed │ ← Harvest processed, fish available
└────┬──────┘
     │
     ▼
┌──────┐
│ Sold │ ← Linked to sales order
└──────┘
```

**Status Enum Colors** (`app/Enums/HarvestStatus.php`):

-   `pending` - ⚠️ Warning (yellow)
-   `completed` - ✅ Success (green)
-   `sold` - 💰 Info (blue)

---

## UI Integration (Filament)

### **Harvests Relation Manager on Sales Order**

Located: `app/Filament/Resources/SalesOrders/RelationManagers/HarvestsRelationManager.php`

**Displays**:

-   Harvest number, date
-   Batch code
-   Unit code
-   Box count (with sum)
-   Total weight (with sum in kg)
-   Total quantity (with sum)
-   Status badge

**Features**:

-   View/Edit actions on individual harvests
-   Filters by status
-   Summarizers showing totals
-   Default sort by harvest_date descending

---

## Data Flow Examples

### **Example 1: Simple Harvest-Then-Sell**

```
1. Batch #B-001 has 5000 fish
2. Create Harvest #H-001
   - batch_id: B-001
   - sales_order_id: NULL
   - total_quantity: 1000
   - total_weight: 250 kg
   - status: pending → completed

3. Create Sales Order #SO-001
   - trader: Customer A
   - Add SalesItem:
     - species: Tilapia
     - quantity: 1000
     - weight_kg: 250
     - unit_price: 50 EGP/kg

4. Link Harvest to Sale
   - Update H-001: sales_order_id = SO-001
   - Status: completed → sold
```

### **Example 2: Order-Fulfillment**

```
1. Receive Order from Customer B
2. Create Sales Order #SO-002
   - Add SalesItem:
     - species: Bass
     - batch_id: B-003 (specify source)
     - quantity: 2000
     - weight_kg: 600
     - fulfillment_status: pending

3. Create Harvest #H-002 to fulfill
   - batch_id: B-003
   - sales_order_id: SO-002 (link immediately)
   - total_quantity: 2000
   - total_weight: 600 kg

4. System tracks fulfillment
   - SalesItem fulfillment_status: pending → fulfilled
```

---

## Missing/Potential Enhancements

Based on code analysis, here are gaps I noticed:

1. **Automatic Fulfillment Update**

    - Currently no observer on Harvest to auto-update SalesItem fulfillment fields
    - When a harvest is created/updated with sales_order_id, should automatically:
        - Update `fulfilled_quantity` and `fulfilled_weight` in matching SalesItem
        - Update `fulfillment_status` based on progress

2. **Batch Quantity Reduction**

    - No automatic reduction of `current_quantity` in Batch when harvest is created
    - Should deduct harvested quantity from batch

3. **Harvest Validation**

    - No check to prevent harvesting more than batch current_quantity
    - Should validate: harvest.total_quantity <= batch.current_quantity

4. **Sales Item → Harvest Creation**

    - No direct UI to create harvest from a sales item
    - Would be useful to have "Create Harvest" action button on unfulfilled sales items

5. **Fulfillment Dashboard**
    - No widget showing pending fulfillments
    - Would help track which orders need harvesting

---

## Summary

The current implementation provides:

-   ✅ Flexible harvest recording (with or without sales orders)
-   ✅ Detailed box-level tracking
-   ✅ Sales order management with line items
-   ✅ Basic linking between harvests and sales
-   ✅ Automatic accounting integration
-   ✅ Cycle closure protection
-   ✅ Automatic pricing calculations

**The flow supports both**:

1. Harvest first, sell later
2. Order first, harvest to fulfill

**Key missing pieces**:

-   Automatic fulfillment tracking updates
-   Batch quantity management on harvest
-   Validation rules for harvest quantities
-   Direct fulfillment workflow UI
