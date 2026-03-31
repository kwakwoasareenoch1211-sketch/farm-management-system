# Comprehensive Expense Tracking Guide

## Overview
Your farm management system now has a unified expense tracking system that automatically captures costs from multiple sources. This eliminates manual data entry and ensures accurate financial reporting.

## How Expense Tracking Works

### Automatic Expense Tracking
The system automatically tracks expenses from these sources:

1. **Feed Usage** 🌾
   - When you record feed usage, the cost is automatically calculated
   - Cost = Quantity (kg) × Unit Cost (from inventory item)
   - Appears in expenses as "Feed: [Feed Name] - Batch: [Batch Name]"

2. **Medication Administration** 💊
   - When you administer medication, the cost is automatically tracked
   - Cost = Quantity Used × Unit Cost
   - Appears in expenses as "Medication: [Med Name] - Batch: [Batch Name]"

3. **Vaccination** 💉
   - When you vaccinate birds, the cost is automatically recorded
   - Cost = Cost Amount (from vaccination record)
   - Appears in expenses as "Vaccination: [Vaccine Name] - Batch: [Batch Name]"

4. **Stock Purchases** 📦
   - When you receive inventory stock, the purchase cost is tracked
   - Cost = Quantity × Unit Cost
   - Appears in expenses as "Stock Purchase: [Item Name] - [Supplier Name]"

5. **Manual Expenses** ✏️
   - Direct expense entries for other costs
   - Utilities, labor, repairs, etc.
   - Full control over category, amount, and details

## Using the Expense Dashboard

### Viewing Expenses
1. Navigate to **Expenses** from the sidebar
2. You'll see:
   - Total expense summary (all-time, this month, today)
   - Breakdown by source with clickable cards
   - Complete expense list with filtering

### Filtering by Source
Click any source card to filter expenses:
- **All Sources** - Shows everything
- **Manual Expenses** - Only direct entries
- **Feed Costs** - Only feed-related expenses
- **Medication Costs** - Only medication expenses
- **Vaccination Costs** - Only vaccination expenses
- **Stock Purchases** - Only inventory purchases

### Understanding the Display

#### Source Badges
Each expense has a colored badge indicating its source:
- 🔵 **Blue** = Manual Expense
- 🟡 **Yellow** = Feed Cost
- 🔴 **Red** = Medication Cost
- 🟢 **Green** = Vaccination Cost
- 🔵 **Cyan** = Stock Purchase

#### Actions
- **Manual Expenses**: Can be edited or deleted
- **Auto-tracked Expenses**: Show "Auto-tracked" - edit the source record instead

## Workflow Examples

### Example 1: Recording Feed Usage
```
1. Go to Feed → Add Feed Record
2. Select inventory item (e.g., "Broiler Starter")
3. Enter quantity (e.g., 50 kg)
4. System automatically:
   - Deducts 50 kg from inventory
   - Calculates cost (50 kg × unit cost)
   - Creates expense record
   - Creates stock movement record
```

**Result:** Expense appears automatically with correct cost

### Example 2: Purchasing Inventory
```
1. Go to Inventory → Receive Stock
2. Select item and supplier
3. Enter quantity and unit cost
4. System automatically:
   - Increases inventory stock
   - Creates expense record for purchase
   - Creates stock receipt record
   - Creates stock movement record
```

**Result:** Purchase cost appears in expenses immediately

### Example 3: Adding Manual Expense
```
1. Go to Expenses → Add Manual Expense
2. Select category (e.g., "Utilities")
3. Enter amount and details
4. Save
```

**Result:** Expense appears in manual expenses list

## Troubleshooting

### "Total amount is higher than expected"
**Cause:** You might be seeing expenses from multiple sources combined.

**Solution:**
1. Click on individual source cards to see breakdown
2. Verify each source separately:
   - Check feed records in Feed section
   - Check medication records in Medication section
   - Check stock purchases in Inventory → Receipts
   - Check manual expenses in Expenses section

### "Can't trace an expense"
**Cause:** Expense might be auto-tracked from another module.

**Solution:**
1. Look at the "Source" column badge
2. If it's not "Manual", go to the source module:
   - Feed → View feed records
   - Medication → View medication records
   - Vaccination → View vaccination records
   - Stock Purchase → Inventory → Receipts

### "Old feed records showing 'Manual'"
**Cause:** These are historical records created before inventory integration.

**Solution:**
- These are safe to keep as historical data
- They don't affect current inventory stock
- Run `cleanup_old_feed_records.php` to review and optionally delete them

## Best Practices

### 1. Use Inventory Items
Always link feed, medication, and vaccination to inventory items:
- Ensures accurate stock tracking
- Automatic cost calculation
- Complete audit trail

### 2. Regular Stock Receipts
Record all inventory purchases immediately:
- Keeps stock levels accurate
- Tracks purchase costs automatically
- Helps with reorder planning

### 3. Categorize Manual Expenses
Use appropriate categories for manual expenses:
- Makes reporting easier
- Better financial analysis
- Clearer expense breakdown

### 4. Review Monthly
Check expense dashboard monthly:
- Verify totals match expectations
- Identify cost trends
- Plan budget adjustments

## Quick Reference

### Navigation
- **View All Expenses**: Expenses → Index
- **Add Manual Expense**: Expenses → Add Expense
- **View Feed Costs**: Feed → Index
- **View Medication Costs**: Medication → Index
- **View Vaccination Costs**: Vaccination → Index
- **View Stock Purchases**: Inventory → Receipts

### Key Reports
- **Expense Report**: Reports → Expenses
- **Profit & Loss**: Accounting → Profit & Loss
- **Financial Dashboard**: Financial → Dashboard
- **Inventory Valuation**: Reports → Inventory Valuation

## Testing Your System

Run these test scripts to verify everything works:

1. **Test Expense System**
   ```
   http://localhost/farmapp/test_expense_system.php
   ```
   - Verifies expense model works
   - Shows breakdown by source
   - Displays sample records

2. **Debug Feed System**
   ```
   http://localhost/farmapp/debug_feed.php
   ```
   - Shows feed records
   - Verifies inventory links
   - Displays stock movements

3. **Cleanup Old Records**
   ```
   http://localhost/farmapp/cleanup_old_feed_records.php
   ```
   - Shows old vs new records
   - Option to delete old records
   - Verifies data integrity

## Support

If you encounter issues:
1. Check the error message carefully
2. Verify database connection
3. Run test scripts to diagnose
4. Check SYSTEM_STATUS.md for system overview
5. Review EXPENSE_SYSTEM_IMPROVEMENTS.md for recent changes
