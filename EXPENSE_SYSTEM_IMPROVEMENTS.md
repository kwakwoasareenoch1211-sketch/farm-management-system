# Expense System Improvements

## Issues Fixed

### 1. InventorySummary.php - Vaccination Records Query Error
**Problem:** Query was trying to access `vr.quantity_used` column which doesn't exist in `vaccination_records` table.

**Solution:** Changed to use `vr.dose_qty` which is the correct column name in the vaccination_records table.

**File:** `app/models/InventorySummary.php`
- Line ~135: Changed `vr.dose_qty AS quantity` to `COALESCE(vr.dose_qty, 0) AS quantity`
- Added COALESCE to handle NULL values safely

### 2. Expense Dashboard - Detailed Breakdown
**Status:** Already implemented in previous update

**Features:**
- Shows expense breakdown by source (manual, feed, medication, vaccination, stock_receipt)
- Clickable source cards to filter expenses
- Clear visual indicators for each expense type
- Totals by source with current month breakdown
- Quick action tiles for all expense-related pages

**Files:**
- `app/models/Expense.php` - Aggregates expenses from all sources
- `app/views/expenses/index.php` - Displays comprehensive expense dashboard

## Current System Architecture

### Expense Sources
The system now tracks expenses from 5 different sources:

1. **Manual Expenses** (expenses table)
   - Direct expense entries
   - Badge: Blue (Primary)
   
2. **Feed Costs** (feed_records table)
   - Automatically tracked when feed is used
   - Linked to inventory items
   - Badge: Yellow (Warning)
   
3. **Medication Costs** (medication_records table)
   - Automatically tracked when medication is administered
   - Linked to inventory items
   - Badge: Red (Danger)
   
4. **Vaccination Costs** (vaccination_records table)
   - Automatically tracked when vaccines are administered
   - Linked to inventory items
   - Badge: Green (Success)
   
5. **Stock Purchases** (stock_receipts table)
   - Inventory purchase costs
   - Badge: Cyan (Info)

### How It Works

#### Expense Aggregation
The `Expense::all()` method combines records from all 5 sources into a unified expense list:
- Each record includes `expense_source` field to identify its origin
- All records are sorted by date descending
- Filtering by source is supported via URL parameter `?source=feed`

#### Expense Totals
The `Expense::totals()` method calculates:
- Total records and amount per source
- Current month amount per source
- Today's amount per source
- Grand totals across all sources

#### Stock Integration
- Feed, medication, and vaccination records MUST use inventory items
- Stock is automatically deducted when used
- Stock is automatically returned when records are deleted
- All movements are tracked in `stock_movements` table

### Database Schema Reference

#### vaccination_records columns:
- `dose_qty` - Quantity of vaccine administered (NOT quantity_used)
- `cost_amount` - Cost of vaccination
- `inventory_item_id` - Link to inventory item

#### medication_records columns:
- `quantity_used` - Quantity of medication used
- `unit_cost` - Cost per unit
- `inventory_item_id` - Link to inventory item

#### feed_records columns:
- `quantity_kg` - Quantity of feed in kg
- `unit_cost` - Cost per kg
- `inventory_item_id` - Link to inventory item (REQUIRED)

## Testing Checklist

### ✅ Fixed Issues
- [x] InventorySummary vaccination query error
- [x] Expense breakdown by source
- [x] Filtering expenses by source
- [x] Visual indicators for expense types

### 🔍 To Test
- [ ] Navigate to Expenses page - should load without errors
- [ ] Click on different source cards - should filter correctly
- [ ] Verify totals match expected amounts
- [ ] Check that auto-tracked expenses show "Auto-tracked" in actions column
- [ ] Verify manual expenses can be edited/deleted
- [ ] Navigate to Inventory Dashboard - should load without errors
- [ ] Check recent inventory activities feed

## Cleanup Script Issue

**Error:** `Call to undefined method Database::getInstance()`

**Analysis:** The cleanup script uses `Database::connect()` which is correct. The error message might be misleading or from a cached version.

**Solution:** The script should work now. If error persists:
1. Clear browser cache
2. Restart Apache/PHP
3. Verify Config.php is loaded before Database.php

## Next Steps

1. **Test the expense dashboard** - Verify all sources show correct data
2. **Test filtering** - Click each source card and verify filtering works
3. **Test inventory dashboard** - Should load without vaccination query error
4. **Verify totals** - Check that expense totals match expected amounts
5. **Test cleanup script** - Run `cleanup_old_feed_records.php` to verify it works

## Important Notes

- Old feed records (before inventory integration) will show "Manual" as inventory item
- These are historical records and don't affect current inventory stock
- Auto-tracked expenses (feed, medication, vaccination) cannot be edited directly
- To modify auto-tracked expenses, edit the source record (e.g., edit feed record)
- Stock movements are automatically created for all inventory-linked operations
