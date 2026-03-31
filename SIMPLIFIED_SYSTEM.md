# Simplified Inventory-Feed System

## User's Request
Remove Stock Receipts, Stock Issues, Low Stock Alerts complexity.

## New Simple Flow

### 1. Inventory Items (Master List)
- Add feed items: Name, Category (feed/medication/other), Unit Cost
- These items are permanent - always available in dropdowns
- No stock tracking, no receipts, no issues

### 2. Feed Module
- Select feed item from dropdown (shows all inventory items)
- Enter quantity used
- Assign to batch
- Save record

### 3. What Gets Removed
- Stock Receipts page
- Stock Issues page  
- Low Stock Alerts
- Stock movements tracking
- current_stock column (not needed)
- reorder_level column (not needed)

## Database Changes

### Keep Simple
```sql
-- inventory_item table (simplified)
- id
- item_name
- category (feed/medication/other)
- unit_cost
- notes
- created_at

-- feed_records table (simplified)
- id
- batch_id
- inventory_item_id (reference to inventory_item)
- quantity_kg
- record_date
- notes
- created_at
```

### Remove Complex Tables
- stock_receipts (delete)
- stock_movements (delete)
- stock_issues (delete)

## User Workflow

### Add Feed Item
1. Go to Inventory → Items
2. Add: "Starter Feed", Category: Feed, Cost: 150 GHS
3. Done - now available everywhere

### Record Feed Usage
1. Go to Feed Module
2. Select "Starter Feed" from dropdown
3. Enter quantity: 50 kg
4. Select batch
5. Save - creates feed record

### View Feed History
- See all feed records
- Edit/Delete as needed
- Items always available in dropdown

## Benefits
- Simple and direct
- No stock tracking complexity
- Items are permanent reference data
- Just track usage, not inventory levels
- Easier to understand and use
