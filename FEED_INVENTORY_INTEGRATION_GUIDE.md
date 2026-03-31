# Feed & Inventory Integration Guide

## System Overview

The feed system is now **fully integrated with inventory**. Feed, medication, and vaccination all use inventory items and automatically track stock movements and expenses.

## How It Works

### 1. Inventory Items (Master Data)
- All feed, medication, and vaccines are stored as **inventory items**
- Each item has: name, category, current stock, unit cost, reorder level
- Stock is tracked in real-time

### 2. Stock Receipts (Receiving Inventory)
- When you receive inventory (purchase feed, medication, etc.)
- Creates a **stock receipt** record
- **Increases** inventory stock
- Creates a **stock movement** (receipt type)
- Automatically creates an **expense** record

### 3. Feed Usage (Consuming Inventory)
- When you record feed usage
- **Requires** selecting an inventory item (no manual entry)
- **Decreases** inventory stock automatically
- Creates a **stock movement** (issue type)
- Automatically creates an **expense** record
- Links to the batch being fed

### 4. Everything is Connected
- **Inventory Dashboard** → Shows all movements (receipts, feed, medication, vaccination)
- **Expense Page** → Shows all costs from all sources
- **Feed Page** → Shows feed usage records
- **Reports** → Unified data across all modules

## Step-by-Step Workflow

### Step 1: Create Inventory Items
1. Go to **Inventory → Add Item**
2. Enter item details:
   - Item Name: e.g., "Broiler Starter Feed"
   - Category: "feed"
   - Unit of Measure: "kg"
   - Current Stock: 0 (you'll add stock in next step)
   - Reorder Level: e.g., 50
   - Unit Cost: e.g., 2.50
3. Save

### Step 2: Receive Stock (Add Inventory)
1. Go to **Inventory → Receive Stock**
2. Select the inventory item you created
3. Enter:
   - Quantity Received: e.g., 100 kg
   - Unit Cost: e.g., 2.50 (auto-filled from item)
   - Receipt Date: today
   - Supplier: (optional)
   - Reference No: (optional)
4. Save

**What happens:**
- Inventory stock increases by 100 kg
- Stock movement created (receipt type)
- Expense record created (100 × 2.50 = GHS 250.00)

### Step 3: Record Feed Usage
1. Go to **Feed → Add Feed Usage**
2. Select:
   - Farm
   - Batch
   - **Inventory Item** (select from dropdown - only items with stock shown)
   - Quantity to Use: e.g., 10 kg
   - Date
3. Save

**What happens:**
- Feed record created
- Inventory stock decreases by 10 kg (now 90 kg remaining)
- Stock movement created (issue type)
- Expense record created (10 × 2.50 = GHS 25.00)
- Shows in inventory dashboard
- Shows in expense page
- Shows in reports

### Step 4: View Integrated Data

**Inventory Dashboard:**
- Shows recent activities including feed usage
- Color-coded badges: Stock (blue), Feed (amber), Medication (red), Vaccination (green)

**Expense Page:**
- Shows all expenses from all sources
- Color-coded badges: Manual (primary), Feed (warning), Medication (danger), Vaccination (success), Stock Purchase (info)

**Feed Page:**
- Shows all feed records
- Linked to inventory items
- Shows batch information

## Troubleshooting

### Error: "Insufficient Stock"

**Cause:** The inventory item doesn't have enough stock

**Solution:**
1. Go to **Inventory → Receive Stock**
2. Add more stock to the item
3. Then try recording feed usage again

### Error: "Not enough stock available"

**Cause:** You're trying to use more than available

**Solution:**
1. Check current stock: **Inventory → All Items**
2. Either:
   - Reduce the quantity you're trying to use, OR
   - Receive more stock first

### Feed Record Won't Delete

**Cause:** Database transaction issue

**Solution:**
- The delete method now properly returns stock to inventory
- If it still fails, check the debug script

### Can't See Inventory Items in Feed Form

**Cause:** No inventory items created yet

**Solution:**
1. Create inventory items first: **Inventory → Add Item**
2. Then receive stock: **Inventory → Receive Stock**
3. Then you can record feed usage

## Debug Script

Run `debug_feed.php` in your browser to see:
- All inventory items and their stock levels
- All feed records
- All stock movements
- System status and recommendations

## Database Schema

### inventory_item
- id, item_name, category, unit_of_measure
- current_stock, reorder_level, unit_cost

### stock_receipts
- id, item_id, supplier_id, quantity, unit_cost
- receipt_date, reference_no, notes

### stock_movements
- id, item_id, movement_type (receipt/issue/adjustment)
- quantity, movement_date, reference_no, notes

### feed_records
- id, farm_id, batch_id, inventory_item_id
- record_date, feed_name, quantity_kg, unit_cost, notes

## Key Points

1. **Inventory items are required** - No more manual feed names
2. **Stock is tracked automatically** - Increases on receipt, decreases on usage
3. **Expenses are auto-created** - From all sources (receipts, feed, medication, vaccination)
4. **Everything is linked** - One unified system across inventory, expenses, and operations
5. **Real-time updates** - Stock levels update immediately
6. **Audit trail** - All movements are logged in stock_movements table

## Benefits

- **Accurate stock tracking** - Always know what you have
- **Complete expense visibility** - See all costs in one place
- **Prevent stockouts** - Low stock alerts
- **Better planning** - Historical usage data
- **Audit compliance** - Complete movement history
- **Integrated reporting** - Unified data across all modules
