# Comprehensive Notifications Plan

## Goal Description

Implement rich, ubiquitous database and Telegram notifications across the entire application to ensure administrators and stakeholders are immediately informed of critical and routine events. Furthermore, we will establish specific Telegram bot commands that can trigger on-demand reports/messages, which will also be recorded as database notifications for auditing.

## Implementation Strategy

We will leverage **Laravel's Event/Observer System** paired with **Filament's native Database Notifications** and a **Custom Telegram Service/Channel**.

### 1. Unified Notification Hub (Laravel Notification Classes)
We will use standard **Laravel Notification classes** to handle multi-channel delivery (`database` and `telegram`). This integrates perfectly with Filament, as we can return Filament's structured notification array from the `toDatabase` method, ensuring the UI remains rich and consistent.

Each notification class (e.g., `BatchClosedNotification`) will implement:
- `via()`: Returning `['database', TelegramChannel::class]`
- `toDatabase()`: Returning `Filament\Notifications\Notification::make()->title('...')->getDatabaseMessage()`
- `toTelegram()` or via a custom channel: Using our existing Telegram services to dispatch the rich message to the appropriate chat.

### 2. Event Triggers (via Observers)

We will hook into the [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34), [updated](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#35-60), and [deleted](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/DailyFeedIssueObserver.php#54-70) methods of our Observers to trigger notifications.

#### **Farm & Operations Module**

- **Batch Lifecycle:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "New Batch [Code] created at Farm [FarmName]."
  - `closed` (Update): "Batch [Code] has been closed. Final Cost: [X], Total Fish: [Y]." (Highly detailed Telegram report + DB notification).
- **Daily Feed Issues:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): Notify if the quantity exceeds an unusually high threshold, or just a daily summary at 8 PM via a scheduled command.
- **Harvest Operations:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Harvest Operation recorded for Batch [Code]. Total Qty: [X] kg."

#### **Financial Module**

- **Petty Cash Transactions:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "New Petty Cash Expense: [Amount] for [Category]."
- **Salary & Advances:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Salary recorded for [Employee]."
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Advance of [Amount] given to [Employee]."
- **Payments:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Payment of [Amount] made to Factory [Name]."
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Batch Payment of [Amount] made to Trader [Name]."

#### **Inventory Module**

- **Feed Stock:**
  - [updated](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#35-60): "Low Stock Alert: [FeedItem] in [Warehouse] dropped below warning threshold."
- **Feed Movements:**
  - [created](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php#13-34): "Feed [Item] received: [Qty] tons at [Warehouse]."

### 3. Telegram Interactive Commands

We will add new commands to [TelegramWebhookHandler.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Http/Controllers/TelegramWebhookHandler.php):

- `/status` or `/farm_summary`: Sends a quick snapshot of active batches and current cash balances.
- `/stock_alert`: Sends current low-stock warnings.
  _(Any command requested via Telegram will also generate a Database Notification to the admin: "User [TelegramName] requested Farm Summary via Bot" to maintain an audit trail)._

## User Review Required

> [!IMPORTANT]  
> Please review the list of events above. Are there any specific operations you consider "too noisy" for Telegram? For example, should we only send Telegram messages for financial transactions above a certain amount, or do you want _everything_?
> Also, which specific Telegram commands would you like us to implement first?

## Proposed Changes

### Core Integration
#### [NEW] `app/Notifications/Channels/TelegramChannel.php` (if needed)
- A custom channel to dispatch notifications via Telegraph to our designated group/admin chat.
#### [NEW] `app/Notifications/BatchClosedNotification.php` (Example)
- Laravel Notification class supporting `database` and our Telegram channel.
#### [NEW] `app/Notifications/LowStockAlertNotification.php`
- Laravel Notification class for feed stock alerts.

### Observers to Modify (Adding Notification Triggers)

#### [MODIFY] [app/Observers/BatchObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/BatchObserver.php)

#### [MODIFY] [app/Observers/DailyFeedIssueObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/DailyFeedIssueObserver.php)

#### [MODIFY] `app/Observers/HarvestOperationObserver.php` (Needs to be created/updated)

#### [MODIFY] [app/Observers/PettyCashTransactionObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/PettyCashTransactionObserver.php)

#### [MODIFY] [app/Observers/SalaryRecordObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/SalaryRecordObserver.php)

#### [MODIFY] [app/Observers/FeedMovementObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/FeedMovementObserver.php)

#### [MODIFY] [app/Observers/FactoryPaymentObserver.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Observers/FactoryPaymentObserver.php)

### Telegram Webhook

#### [MODIFY] [app/Http/Controllers/TelegramWebhookHandler.php](file:///d:/HybatAquaFarms/HybatAquaFarms/app/Http/Controllers/TelegramWebhookHandler.php)

- Add logic to parse new commands (`/status`, `/stock`) and respond utilizing existing or new ReportServices, plus log the request to the DB.

## Verification Plan

### Automated Tests

- Create tests for `NotificationDispatchService` asserting that Filament Database notifications are created.
- Mock the Telegram HTTP wrapper to assert Telegram messages are formatted correctly.

### Manual Verification

1. Create a mock record (e.g., Petty Cash) and verify a notification appears in the Filament Admin panel.
2. Check the Telegram channel/bot to ensure the corresponding message is received.
3. Send a command like `/status` to the bot and verify the response and the subsequent DB audit notification.
