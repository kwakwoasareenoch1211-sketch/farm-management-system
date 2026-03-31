# Expense Totals Discrepancy - FIXED ✓

## Problem
The Expenses page was showing **GHS 1,043** while the Financial dashboard showed **GHS 3,083** - a discrepancy of **GHS 2,040**.

## Root Cause
The `Expense::totals()` method was NOT including:
- Livestock Purchase Cost: GHS 1,800 (cash paid for chicks)
- Mortality Loss: GHS 240 (asset write-off)

The `FinancialMonitor::buildExpenses()` method WAS including these, causing the mismatch.

## Solution Applied

### 1. Updated `app/models/Expense.php`
Added two new expense categories to the `totals()` method:

```php
// Livestock purchase cost (cash paid for chicks)
$stmt = $this->db->query("
    SELECT
        COUNT(*) AS cnt,
        COALESCE(SUM(initial_quantity * initial_unit_cost), 0) AS total,
        COALESCE(SUM(CASE WHEN YEAR(start_date) = YEAR(CURDATE()) AND MONTH(start_date) = MONTH(CURDATE()) THEN initial_quantity * initial_unit_cost ELSE 0 END), 0) AS current_month,
        COALESCE(SUM(CASE WHEN start_date = CURDATE() THEN initial_quantity * initial_unit_cost ELSE 0 END), 0) AS today
    FROM animal_batches
    WHERE initial_unit_cost IS NOT NULL AND initial_unit_cost > 0
");
$totals['livestock_purchase'] = [...];

// Mortality loss (asset write-off)
$stmt = $this->db->query("
    SELECT
        COUNT(*) AS cnt,
        COALESCE(SUM(mr.quantity * ab.initial_unit_cost), 0) AS total,
        COALESCE(SUM(CASE WHEN YEAR(mr.record_date) = YEAR(CURDATE()) AND MONTH(mr.record_date) = MONTH(CURDATE()) THEN mr.quantity * ab.initial_unit_cost ELSE 0 END), 0) AS current_month,
        COALESCE(SUM(CASE WHEN mr.record_date = CURDATE() THEN mr.quantity * ab.initial_unit_cost ELSE 0 END), 0) AS today
    FROM mortality_records mr
    INNER JOIN animal_batches ab ON ab.id = mr.batch_id
");
$totals['mortality_loss'] = [...];
```

Also fixed duplicate return statement in the method.

### 2. Updated `app/views/expenses/index.php`
Added the new expense categories to the display:

```php
$sourceConfig = [
    'manual' => ['label' => 'Manual Expenses', 'color' => '#0d6efd', 'icon' => 'bi-pencil-square'],
    'livestock_purchase' => ['label' => 'Livestock Purchase Cost', 'color' => '#6f42c1', 'icon' => 'bi-egg'],
    'mortality_loss' => ['label' => 'Mortality Loss', 'color' => '#d63384', 'icon' => 'bi-heartbreak'],
    'feed' => ['label' => 'Feed Costs', 'color' => '#ffc107', 'icon' => 'bi-basket'],
    'medication' => ['label' => 'Medication Costs', 'color' => '#dc3545', 'icon' => 'bi-capsule'],
    'vaccination' => ['label' => 'Vaccination Costs', 'color' => '#198754', 'icon' => 'bi-shield-check'],
];
```

Updated Quick Actions to include:
- Livestock Purchase (links to /batches)
- Mortality Loss (links to /mortality)

Removed obsolete Stock Purchases link (inventory tracking was unified).

## Verification Results

```
=== EXPENSE TOTALS VERIFICATION ===

EXPENSE MODEL TOTALS:
--------------------
Total Amount: GHS 3,083.00
Total Records: 10

BREAKDOWN BY SOURCE:
  Manual: 3 records, GHS 498.00
  Feed: 1 records, GHS 395.00
  Medication: 1 records, GHS 150.00
  Livestock purchase: 1 records, GHS 1,800.00
  Mortality loss: 4 records, GHS 240.00

FINANCIAL MONITOR TOTALS:
-------------------------
Total Expenses: GHS 3,083.00

=== COMPARISON ===
✓ MATCH! Both show GHS 3,083.00
✓ Expense page and Financial dashboard are now consistent!
```

## Expense Breakdown
- Manual Expenses: GHS 498.00 (3 records)
- Livestock Purchase Cost: GHS 1,800.00 (1 batch of chicks)
- Mortality Loss: GHS 240.00 (4 mortality records)
- Feed Costs: GHS 395.00 (1 record)
- Medication Costs: GHS 150.00 (1 record)
- Vaccination Costs: GHS 0.00 (0 records)
- **TOTAL: GHS 3,083.00**

## Accounting Principles Applied

### Livestock Purchase Cost
- When you buy chicks, the **cash paid** is recorded as an **Expense** (reduces profit)
- The **live birds** are recorded as a **Biological Asset** (increases assets)
- This is correct double-entry accounting: Debit Expense, Debit Asset, Credit Cash

### Mortality Loss
- When birds die, the **asset value is lost** - this is an **Expense** (loss)
- The biological asset is reduced by the value of dead birds
- The loss is written off as an expense
- Formula: `mortality_quantity × initial_unit_cost`

## Files Modified
1. `app/models/Expense.php` - Added livestock_purchase and mortality_loss to totals()
2. `app/views/expenses/index.php` - Updated display to show new categories
3. `verify_expense_totals.php` - Created verification script

## Testing
Run: `php verify_expense_totals.php`

## Status
✓ FIXED - Expenses page now shows GHS 3,083.00, matching Financial dashboard
✓ All expense sources are now included in totals
✓ Display updated to show livestock purchase and mortality loss categories
✓ Accounting principles correctly applied
