# Application Analysis & Development Guidelines

## 1. System Architecture Overview

The application is a robust ERP system tailored for aquaculture management (Fish Farms), built on **Laravel 11** and **Filament v3**. It follows a modular architecture where operational logic is separated from the core domain logic.

### Key Directories

-   **`app/Filament/Resources`**: Contains the UI/UX logic, grouped by domain (e.g., `Accounting`, `Employees`, `Farms`).
-   **`app/Domain`**: Houses complex business logic services (e.g., `Accounting/PostingService`) that are independent of the UI.
-   **`app/Enums`**: Centralized definitions for statuses and types, implementing Filament interfaces for seamless UI integration.
-   **`database/migrations`**: Defines a relational schema with a strong focus on data integrity and audit trails.

---

## 2. The Financial Engine (Core Domain)

The application implements a **Double-Entry Accounting System** that is event-driven and configuration-based. This is the most critical subsystem to understand for any financial extensions.

### 2.1. The Posting Mechanism

Instead of hardcoding accounting logic (e.g., "Debit Cash, Credit Sales") inside controllers, the system uses a **Rule-Based Approach**.

1.  **`posting_rules` Table**: Defines how business events map to accounting entries.

    -   `event_key`: A unique string identifier (e.g., `voucher.payment`, `sales.cash`).
    -   `debit_account_id` / `credit_account_id`: The GL accounts to be impacted.
    -   `description`: Default description for the entry.

2.  **`PostingService`**: The domain service responsible for executing these rules.
    -   **Method**: `post(string $eventKey, array $context)`
    -   **Context**: Passes dynamic data like `amount`, `date`, `farm_id`, and `source` (the model triggering the event).

### 2.2. Journal Entries

All financial transactions result in a `JournalEntry` with multiple `JournalLine` items.

-   **Polymorphic Source**: The `journal_entries` table uses `source_type` and `source_id` to link back to the operational record (e.g., a `Voucher`, `SalesOrder`, or `Payroll`).
-   **Immutability**: Once posted (`is_posted = true`), entries should generally be immutable to preserve audit trails.

### 2.3. Vouchers

Vouchers represent the physical or digital proof of a transaction (Receipts/Payments).

-   They are linked to a `counterparty` (Employee, Trader, etc.) via polymorphism.
-   They serve as the _trigger_ for the `PostingService`.

---

## 3. Data Modeling Patterns

### 3.1. Enums as First-Class Citizens

The application heavily relies on PHP Enums located in `app/Enums`.

-   **Interfaces**: Enums implement `HasLabel`, `HasColor`, and `HasIcon`.
-   **Usage**: This allows Filament to automatically render badges, icons, and localized text without extra code in the Resource classes.
-   **Example**: `AccountType`, `VoucherType`, `PaymentMethod`.

### 3.2. Scoped Multi-Tenancy (Farm-Centric)

Most operational entities (`Batches`, `FeedStocks`, `Vouchers`, `Accounts`) are scoped to a `farm_id`.

-   **Guideline**: Always ensure new entities that belong to a specific farm include a `farm_id` foreign key and index.

### 3.3. Polymorphism

The system uses polymorphic relationships to reduce schema redundancy.

-   **Counterparties**: A single `Voucher` can pay an `Employee`, `Trader`, or `Driver`.
-   **Sources**: A `JournalEntry` can originate from anywhere.

---

## 4. Guidelines for Extending the System

When adding new features (e.g., new financial modules, inventory types), follow these steps:

### Step 1: Database Design

-   Create migrations in `database/migrations`.
-   If the feature involves money, **DO NOT** add standalone "balance" columns. Instead, plan to link it to the `journal_entries` system.
-   Use `foreignId` constraints strictly.

### Step 2: Define Enums

-   Create new Enums in `app/Enums` for any status or type fields.
-   Implement `HasLabel`, `HasColor`, and `HasIcon` immediately to save time on UI work later.

### Step 3: Configure Posting Rules

-   If the new feature generates financial transactions, define new `event_key`s (e.g., `treasury.deposit`, `treasury.withdrawal`).
-   Add a migration to insert these default rules into the `posting_rules` table.

### Step 4: Implement Domain Logic

-   Use `App\Domain\Accounting\PostingService` to handle the financial side effects.
-   **Example**:
    ```php
    $postingService->post('new_feature.action', [
        'amount' => $amount,
        'source_type' => NewFeatureModel::class,
        'source_id' => $model->id,
        'farm_id' => $model->farm_id,
    ]);
    ```

### Step 5: Filament UI

-   Generate Resources using `php artisan make:filament-resource`.
-   Use `RelationManagers` to show related financial records (e.g., "Journal Entries" tab on the new feature's view page).

---

## 5. Summary of Existing Financial Structures

-   **Accounts**: The Chart of Accounts.
-   **Vouchers**: Operational record of payments/receipts.
-   **PettyCash**: Management of small cash funds.
-   **JournalEntry**: The source of truth for all accounting.
-   **PostingRule**: The bridge between Operations and Accounting.
