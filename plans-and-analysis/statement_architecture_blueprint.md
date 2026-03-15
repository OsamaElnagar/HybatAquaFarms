# Architecture Summary: Session-Based Ledger Statements

This document summarizes the unified accounting architecture implemented for **Traders**. This pattern is designed to simplify lifelong balance tracking by breaking it into manageable, titled "sessions" or "cycles."

## 1. Core Concept
Instead of one infinite ledger, every transaction is linked to a **Session** (e.g., [TraderStatement](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/TraderStatement.php#13-99)).
- A session has an **Opening Balance** (carried over from the previous session's closing).
- A session has a **Status** (Open/Closed).
- Transactions are filtered by the **Active Session** by default in the UI.

## 2. Model & Database Patterns
- **Statement Model**: [TraderStatement](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/TraderStatement.php#13-99) stores `trader_id`, `opened_at`, `closed_at`, `opening_balance`, `closing_balance`, `status`, and metadata (`title`, `notes`).
- **Unified Ledger**: All financial events (Sales, Cash, Loans) are recorded as [JournalEntry](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/JournalEntry.php#14-76) and [JournalLine](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/JournalLine.php#12-50).
- **The Link**: [JournalEntry](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/JournalEntry.php#14-76) contains a nullable `trader_statement_id` (or equivalent entity FK).
- **Linking Entities**: Statements can be linked to operational models (e.g., [HarvestOperation](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/HarvestOperation.php#15-122)) via pivot tables to group financial outcomes by physical production cycles.

## 3. Automation & Lifecycle
- **Auto-Initialization**: A [TraderObserver](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Observers/TraderObserver.php#8-48) creates the first "Initial Session" upon trader creation.
- **Closing/Opening**: When a new session is opened (via `Trader::openNewStatement()`):
    1. The currently open session's `closing_balance` is snapshotted.
    2. The status is set to `Closed`.
    3. A new session is created with the snapshot as its `opening_balance`.
- **Automatic Linking**: Observers (e.g., [SalesOrderObserver](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Observers/SalesOrderObserver.php#8-37)) automatically pull the `activeStatement?->id` from the trader and attach it to new [JournalEntry](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/JournalEntry.php#14-76) records.

## 4. Reusable Accounting Action Pattern
To ensure consistency across Tables, View pages, and Edit pages, core transactions are refactored into **Reusable Action Classes** (extending `Filament\Actions\Action`):
- [ReceivePaymentAction](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Traders/Actions/ReceivePaymentAction.php#14-62): Handles Debit Treasury / Credit Trader ledger entry.
- [GiveCashAction](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Traders/Actions/GiveCashAction.php#14-61): Handles Debit Trader / Credit Treasury ledger entry.
- [OpenNewStatementAction](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Filament/Resources/Traders/Actions/OpenNewStatementAction.php#13-59): Handles the modal form and logic for session transition.
- **Benefits**: Centralized validation, consistent labels/icons, and identical business logic regardless of where the action is triggered.

## 5. UI Implementation (Filament)
- **StatementOfAccount Page**: 
    - Defaults `activeStatementId` to the current open session.
    - Query filters [JournalLine](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/JournalLine.php#12-50) WHERE `journalEntry->trader_statement_id == activeStatementId`.
    - Includes a "View All History" toggle that nullifies the filter.
    - Displays session metadata (opening balance, title, date range) as a header section.
- **ListStatements Page**: A standard table resource to browse and search the history of past sessions with direct links to view them.

## 6. Deprecation of Niche Models
- Legacy specific models (like [PartnerLoan](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/PartnerLoan.php#15-73)) were deprecated for traders.
- Instead, these interactions are now **Direct Ledger Entries** categorized via specific [Account](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/Account.php#14-98) IDs or entry descriptions, all flowing through the same [TraderStatement](file:///f:/HybatAquaFarms/HybatAquaFarms/app/Models/TraderStatement.php#13-99) session.
