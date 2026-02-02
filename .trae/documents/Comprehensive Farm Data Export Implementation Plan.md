# Implement Comprehensive Farm Data Export Feature

The goal is to implement a robust, multi-sheet Excel export for the Farm resource that captures the entire farm ecosystem, including related data like batches, employees, and financial records.

## Technical Implementation Plan

### 1. Create the Farm Exporter
I will create a new exporter class `app/Filament/Exports/FarmExporter.php` using the Filament command. This class will handle:
- **Main Farm Sheet**: Defining columns for the primary farm data (code, name, manager, size, location, status, etc.).
- **Export Options**: A form with checkboxes allowing users to select which related data sets to include (e.g., Batches, Employees, Units, Feed Issues, Transactions).
- **Multi-Sheet Logic**: Leveraging the `configureXlsxWriterBeforeClose` hook to add additional sheets for each selected relationship.
- **Summary Sheet**: A dedicated sheet summarizing key metrics across the exported farms (total area, total stock, active batches count).

### 2. Structured Data Extraction
The export process will:
- **Capture Query State**: The `ExportAction` will pass the serialized Eloquent query (including filters and sorting) to the exporter.
- **Efficient Loading**: Use eager loading (`with(['batches', 'employees', ...])`) and chunked processing to ensure performance even with large datasets.
- **Relational Integrity**: Extract data from all primary relation managers:
    - `Batches`: Code, species, status, quantity, cost.
    - `Employees`: Name, role, contact info.
    - `Units`: Code, size, type.
    - `DailyFeedIssues`: Date, item, quantity.
    - `ExternalCalculations`: Calculation details and results.
    - `PettyCashTransactions`: Amount, description, date.

### 3. Action Integration
I will integrate the `ExportAction` into the `FarmsTable` header actions. 
- It will support both CSV and XLSX formats (with XLSX supporting multiple sheets).
- It will provide real-time progress feedback and system notifications upon completion.

### 4. Security & Performance
- **Permissions**: The export logic will respect Laravel policies to ensure users only export data they are authorized to view.
- **Optimization**: Leveraging Filament's background job batching system to prevent timeouts and server strain.
- **Cleanup**: Utilizing Filament's built-in pruning to automatically delete old export files.

### 5. Verification & Testing
- **Unit Tests**: Testing the data extraction logic to ensure correct relationship mapping.
- **Feature Tests**: Simulating the export process in a Pest test to verify workbook structure and content.
- **Manual Verification**: Testing the UI flow in the Filament panel.

## Key Deliverables
- `app/Filament/Exports/FarmExporter.php`: The core export engine.
- Updated `app/Filament/Resources/Farms/Tables/FarmsTable.php`: Integrated export action.
- `tests/Feature/Farms/FarmExportTest.php`: Comprehensive test suite.

Does this plan meet your requirements? I am ready to start implementation once you confirm.