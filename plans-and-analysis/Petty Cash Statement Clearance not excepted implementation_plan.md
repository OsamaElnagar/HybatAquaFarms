# Petty Cash Statement Clearance Implementation Plan

This plan outlines the implementation of a "Statement Clearance" (تصفية عهدة) feature for Petty Cash, following the standard session-based architecture used for Traders and Factories.

## User Review Required

> [!IMPORTANT]
> - This feature introduces a structural change: all `PettyCashTransactions` will now be linked to a `PettyCashStatement`.
> - A migration will be needed to link existing transactions to a newly created "Initial Statement" for each Petty Cash.

## Proposed Changes

### Database Layer

#### [NEW] [create_petty_cash_statements_table](file:///f:/HybatAquaFarms/HybatAquaFarms/database/migrations/2026_03_17_000000_create_petty_cash_statements_table.php)
- `id`, `petty_cash_id`, `title`, `created_by`, `opened_at`, `closed_at`, `opening_balance`, `closing_balance`, `status`, `notes`.

#### [MODIFY] [add_statement_id_to_petty_cash_transactions_table](file:///f:/HybatAquaFarms/HybatAquaFarms/database/migrations/2026_03_17_000001_add_statement_id_to_petty_cash_transactions_table.php)
- Add `petty_cash_statement_id` FK (nullable).

---

### App Layer

#### [NEW] [PettyCashStatementStatus](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Enums/PettyCashStatementStatus.php)
- Enum with [Open](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/FactoryStatement.php#84-91) and `Closed` (or `Cleared`) statuses.

#### [NEW] [PettyCashStatement](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashStatement.php)
- Model with relationships to [PettyCash](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCash.php#12-63), `User` (creator), and [PettyCashTransaction](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashTransaction.php#14-78).
- Business logic for `total_in`, `total_out`, and `net_balance`.

#### [NEW] [PettyCashObserver](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Observers/PettyCashObserver.php)
- Automatically creates the first "Initial Statement" when a new Petty Cash is created.

#### [MODIFY] [PettyCashTransactionObserver](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Observers/PettyCashTransactionObserver.php)
- Auto-link transactions to the active (open) statement of the associated Petty Cash.

---

### UI Layer (Filament)

#### [NEW] [PettyCashStatementResource](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/PettyCashStatementResource.php)
- Resource to view and manage past statements.

#### [MODIFY] [PettyCashResource](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/PettyCashes/PettyCashResource.php)
- Add "Clear Statement" action that closes the current statement and opens a new one.
- Filter transactions by the active statement in the relation manager.

#### [MODIFY] [PettyCashTransactionForm](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/PettyCashTransactions/Schemas/PettyCashTransactionForm.php)
- Ensure the statement ID is handled correctly.

## Verification Plan

### Automated Tests
- Create a new Pest test `tests/Feature/PettyCashStatementTest.php`.
- Test:
    - Creating a [PettyCash](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCash.php#12-63) auto-creates an open `PettyCashStatement`.
    - Creating a [PettyCashTransaction](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PettyCashTransaction.php#14-78) auto-links it to the active statement.
    - Closing a statement snapshots the balance and opens a new one with those funds.
- Command: `php artisan test tests/Feature/PettyCashStatementTest.php`

### Manual Verification
- Go to Petty Cash resource.
- Add some transactions.
- Verify they appear in the "Current Statement".
- Click "Clear Statement".
- Verify a new statement is opened and the balance carries over.
