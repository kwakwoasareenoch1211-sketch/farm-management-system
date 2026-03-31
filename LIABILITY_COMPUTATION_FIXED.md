# Liability Computation Fixed ✓

## Problem
The liabilities page was showing an unpaid expense with the wrong amount:
- Expense description stated: "Total Calculation: 50 + 200 + 90 + 150 + 25 + 8 + 30 + 25 = 578"
- But the stored amount was: GHS 378.00
- This was a data entry error (GHS 200 difference)

## Root Cause
The expense was manually entered with an incorrect amount (GHS 378 instead of GHS 578), even though the description clearly showed the calculation should total GHS 578.

## Solution Applied

### 1. Fixed Expense Amount
Updated expense ID 3 from GHS 378.00 to GHS 578.00 to match the calculation in the description.

```sql
UPDATE expenses SET amount = 578.00 WHERE id = 3;
```

### 2. Created Missing Liability
The unpaid expense didn't have a corresponding liability record, so we created one:

```sql
INSERT INTO liabilities (
    source_type, source_id, liability_name, liability_type,
    principal_amount, outstanding_balance, start_date, status
) VALUES (
    'expense', 3, 'Unpaid Expense: Various Items', 'other',
    578.00, 578.00, '2026-01-22', 'active'
);
```

### 3. Updated Expense Model
Enhanced the `unpaid()` method to include liability_id for linking:

```php
public function unpaid(): array
{
    $stmt = $this->db->query("
        SELECT 
            e.*,
            COALESCE(ec.category_name, 'Uncategorized') AS category_name,
            (e.amount - e.amount_paid) AS outstanding_amount,
            l.id AS liability_id  // Added this line
        FROM expenses e
        LEFT JOIN expense_categories ec ON ec.id = e.category_id
        LEFT JOIN liabilities l ON l.source_type = 'expense' AND l.source_id = e.id
        WHERE e.payment_status IN ('unpaid', 'partial')
        ORDER BY e.expense_date ASC, e.id ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
```

## Verification Results

### Expense Data
```
ID: 3
Description: Akins = 50, Station car = 200, Taxi = 90, Ntete = 150, Net = 25, Nails = 8, Adam and Eve = 30, Spraying medicine = 25
Amount: GHS 578.00 ✓
Amount Paid: GHS 0.00
Outstanding: GHS 578.00
Status: unpaid
```

### Liability Data
```
ID: 6
Name: Unpaid Expense: Various Items
Principal: GHS 578.00 ✓
Outstanding Balance: GHS 578.00 ✓
Status: active
Total Payments: GHS 0
Calculated Outstanding: GHS 578.00 ✓
```

### Liability Totals
```
Total Liabilities: 1
Active Liabilities: 1
Total Principal: GHS 578.00
Total Outstanding: GHS 578.00
Active Outstanding: GHS 578.00
```

### Updated Expense Totals
After fixing the amount, total expenses increased from GHS 3,083 to GHS 3,283:
```
Manual Expenses: GHS 698.00 (was 498.00, +200.00)
Feed Costs: GHS 395.00
Medication Costs: GHS 150.00
Livestock Purchase: GHS 1,800.00
Mortality Loss: GHS 240.00
TOTAL: GHS 3,283.00 ✓
```

## Expense Breakdown (ID 3)
The corrected expense includes:
- Akins: GHS 50
- Station car: GHS 200
- Taxi (dropping): GHS 90
- Ntete: GHS 150
- Net: GHS 25
- Nails: GHS 8
- Adam and Eve, padlock and related items: GHS 30
- Spraying medicine: GHS 25
- **TOTAL: GHS 578** ✓

## Accounting Impact

### Before Fix
- Expenses: GHS 3,083
- Liabilities: GHS 0 (no liability record)
- Discrepancy: GHS 200 missing from expense

### After Fix
- Expenses: GHS 3,283 (+200)
- Liabilities: GHS 578 (new liability created)
- All amounts now accurate ✓

## Files Modified
1. `app/models/Expense.php` - Updated unpaid() method to include liability_id
2. Database - Fixed expense amount and created liability record

## Testing
Run verification scripts:
- `php verify_liability_fix.php` - Verifies liability data
- `php verify_expense_totals.php` - Verifies expense totals

## Status
✓ FIXED - Expense amount corrected to GHS 578.00
✓ FIXED - Liability created with correct principal amount
✓ FIXED - Unpaid expenses now link to their liabilities
✓ VERIFIED - All computations are accurate
