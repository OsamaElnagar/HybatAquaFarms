# Auto-Generated Code/Number Implementation Summary

## Overview

Implemented automatic code/number generation for multiple models to ensure unique patterns and consistency across the application.

## Models with Auto-Generation

### 1. **Account** (`code`)

-   **Observer**: `AccountObserver`
-   **Pattern**: Type-based prefix + 4-digit number
    -   Assets: `AST-0001`
    -   Liabilities: `LIA-0001`
    -   Equity: `EQT-0001`
    -   Income: `INC-0001`
    -   Expense: `EXP-0001`
-   **Form**: Disabled field with "يتم توليده تلقائياً" helper text

### 2. **JournalEntry** (`entry_number`)

-   **Observer**: `JournalEntryObserver`
-   **Pattern**: `JE-000001` (6 digits)
-   **Form**: N/A (no existing form found)

### 3. **Employee** (`employee_number`)

-   **Observer**: `EmployeeObserver`
-   **Pattern**: `EMP-0001` (4 digits)
-   **Form**: Disabled field with auto-generation helper text

### 4. **EmployeeAdvance** (`advance_number`)

-   **Observer**: `EmployeeAdvanceObserver` (enhanced)
-   **Pattern**: `ADV-00001` (5 digits)
-   **Form**: Disabled field with auto-generation helper text

### 5. **ExpenseCategory** (`code`)

-   **Observer**: `ExpenseCategoryObserver`
-   **Pattern**: `EXC-001` (3 digits)
-   **Form**: Disabled field with auto-generation helper text

### 6. **Factory** (`code`)

-   **Observer**: `FactoryObserver`
-   **Pattern**: `FAC-001` (3 digits)
-   **Form**: Disabled field with auto-generation helper text

### 7. **Farm** (`code`)

-   **Observer**: `FarmObserver`
-   **Pattern**: `FRM-001` (3 digits)
-   **Form**: Disabled field with auto-generation helper text

### 8. **FarmUnit** (`code`)

-   **Observer**: `FarmUnitObserver`
-   **Pattern**: `UNIT-001` (3 digits, scoped by farm)
-   **Form**: Disabled field with auto-generation helper text

### 9. **FeedItem** (`code`)

-   **Observer**: `FeedItemObserver`
-   **Pattern**: `FEED-001` (3 digits)
-   **Form**: Disabled field with auto-generation helper text

### 10. **SalesOrder** (`order_number`)

-   **Observer**: `SalesOrderObserver` (already existed)
-   **Pattern**: `SO-00001` (5 digits)
-   **Form**: Disabled field with auto-generation helper text

## Implementation Details

### Observer Registration

All observers are registered using the `#[ObservedBy]` attribute on models:

```php
#[ObservedBy([ModelObserver::class])]
class Model extends Model
{
    // ...
}
```

### Auto-Generation Logic

Each observer implements a `creating()` method that:

1. Checks if the code/number field is empty
2. Generates a unique code based on the last record
3. Assigns it to the model before saving

Example:

```php
public function creating(Model $model): void
{
    if (! $model->code) {
        $model->code = static::generateCode();
    }
}

protected static function generateCode(): string
{
    $lastRecord = Model::latest('id')->first();
    $number = $lastRecord ? ((int) substr($lastRecord->code, 4)) + 1 : 1;

    return 'PRE-' . str_pad($number, 3, '0', STR_PAD_LEFT);
}
```

### Form Field Updates

All Filament form fields for auto-generated codes are now:

-   **Disabled**: Users cannot edit them
-   **Dehydrated(false)**: Not included in form submission
-   **Helper Text**: Shows "يتم توليده تلقائياً" (Generated automatically)

## Files Created

-   `app/Observers/AccountObserver.php`
-   `app/Observers/JournalEntryObserver.php`
-   `app/Observers/EmployeeObserver.php`
-   `app/Observers/ExpenseCategoryObserver.php`
-   `app/Observers/FactoryObserver.php`
-   `app/Observers/FarmObserver.php`
-   `app/Observers/FarmUnitObserver.php`
-   `app/Observers/FeedItemObserver.php`

## Files Modified

### Models

-   `app/Models/Account.php`
-   `app/Models/JournalEntry.php`
-   `app/Models/Employee.php`
-   `app/Models/ExpenseCategory.php`
-   `app/Models/Factory.php`
-   `app/Models/Farm.php`
-   `app/Models/FarmUnit.php`
-   `app/Models/FeedItem.php`

### Observers

-   `app/Observers/EmployeeAdvanceObserver.php` (enhanced)

### Forms

-   `app/Filament/Resources/Accounts/Schemas/AccountForm.php`
-   `app/Filament/Resources/Employees/Schemas/EmployeeForm.php`
-   `app/Filament/Resources/EmployeeAdvances/Schemas/EmployeeAdvanceForm.php`
-   `app/Filament/Resources/ExpenseCategories/Schemas/ExpenseCategoryForm.php`
-   `app/Filament/Resources/Factories/Schemas/FactoryForm.php`
-   `app/Filament/Resources/Farms/Schemas/FarmForm.php`
-   `app/Filament/Resources/FarmUnits/Schemas/FarmUnitForm.php`
-   `app/Filament/Resources/FeedItems/Schemas/FeedItemForm.php`
-   `app/Filament/Resources/SalesOrders/Schemas/SalesOrderForm.php`

## Benefits

1. **Consistency**: All codes follow a predictable pattern
2. **Uniqueness**: Automatic sequential numbering prevents duplicates
3. **User Experience**: Users don't need to manually enter codes
4. **Data Integrity**: Reduces human error in code entry
5. **Scalability**: Easy to extend to other models

## Testing Recommendations

1. Create new records for each model to verify auto-generation
2. Check that codes are sequential and unique
3. Verify form fields are disabled and show helper text
4. Test with existing data to ensure no conflicts
5. Verify seeders still work correctly
