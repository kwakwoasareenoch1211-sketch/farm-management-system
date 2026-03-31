# Unified Inventory-Feed System

## Core Concept: ONE System, Not Two

Inventory and feed are **NOT separate systems that are linked**. They are **ONE UNIFIED SYSTEM**.

- Inventory items ARE feed items
- There is no separate "feed" entity
- Feed records are just usage records of inventory items
- No duplication, no linking, no syncing - just ONE system

## How It Works

### Single Source of Truth: Inventory
Everything starts and lives in the inventory system:
- Item name
- Category
- Unit of measure
- Current stock
- Unit cost
- Reorder level

### Feed Records = Usage Records
When you "record feed usage", you're simply recording:
- Which inventory item was used
- How much was used
- Which batch received it
- When it happened

The feed record doesn't store item names or costs - it just references the inventory item ID. All data comes from the single source of truth.

## Database Structure

### inventory_item (Master Table)
```sql
- id
- item_name          ← Single source of truth
- category
- unit_of_measure
- current_stock      ← Updated by receipts and usage
- reorder_level
- unit_cost          ← Single source of truth
```

### feed_records (Usage Records)
```sql
- id
- inventory_item_id  ← REQUIRED foreign key (NOT NULL)
- batch_id
- quantity_kg        ← How much was used
- record_date
- feed_name          ← Redundant, populated from inventory_item.item_name
- unit_cost          ← Redundant, populated from inventory_item.unit_cost
- notes
```

Note: `feed_name` and `unit_cost` in feed_records are redundant copies for reporting convenience. The real data always comes from inventory_item.

## Workflow

### 1. Add Item to Unified System
**Location:** Inventory → Items → Add Item

```
Create: "Broiler Starter Feed"
Category: feed
Unit: kg
Cost: GHS 395/bag
```

This item now exists in the unified system. It's not "added to inventory and then synced to feed" - it simply exists as one item.

### 2. Receive Stock
**Location:** Inventory → Receive Stock

```
Item: Broiler Starter Feed
Quantity: 50 kg
```

Stock is updated in the unified system. The item is now available for use.

### 3. Record Usage
**Location:** Feed → Record Usage

```
Item: Broiler Starter Feed (from unified system)
Batch: Batch A
Quantity: 10 kg
```

This creates a usage record. Stock is deducted from the unified system.

## Key Principles

### 1. No Duplication
- You don't create an item in inventory AND in feed
- You create ONE item in the unified system
- That item can be used for feed, medication, or any other purpose

### 2. No Linking/Syncing
- There's no "sync" process
- There's no "linking" step
- The feed record directly references the inventory item
- It's one system, not two systems connected

### 3. Single Source of Truth
- Item name? From inventory_item table
- Unit cost? From inventory_item table
- Current stock? From inventory_item table
- Everything comes from ONE place

### 4. Usage Records, Not Separate Entities
- Feed records are usage records
- Medication records are usage records
- Stock issues are usage records
- They all reference the same unified inventory

## Benefits

1. **Simplicity**: One system to manage, not two
2. **Consistency**: One source of truth for all data
3. **No Sync Issues**: Can't get out of sync when there's only one system
4. **Flexibility**: Same items can be used for multiple purposes
5. **Accuracy**: Stock levels always correct because there's only one place they're stored

## User Mental Model

### ❌ Wrong Mental Model:
"I create feed items in inventory, then they sync to the feed system"

### ✓ Correct Mental Model:
"I create items in the system. When I use them for feeding, I record that usage. The system tracks everything as one."

## Technical Implementation

### Feed Model
```php
// Get item data from inventory (single source of truth)
$item = $itemModel->find($inventoryItemId);
$feedName = $item['item_name'];  // From inventory
$unitCost = $item['unit_cost'];  // From inventory

// Create usage record
INSERT INTO feed_records (
    inventory_item_id,  // Required - links to unified system
    feed_name,          // Copy for convenience
    unit_cost,          // Copy for convenience
    quantity_kg,        // Usage amount
    ...
)
```

### Stock Management
```php
// Deduct from unified system
UPDATE inventory_item 
SET current_stock = current_stock - quantity
WHERE id = inventory_item_id
```

## Migration from Old System

Old systems may have had:
- Separate feed items table
- Manual feed name entry
- Separate stock tracking

The unified system eliminates all of this. Feed records without `inventory_item_id` are marked as "Legacy" and don't affect the unified inventory.

## Summary

**Inventory and feed are not two systems that are linked. They are ONE UNIFIED SYSTEM where inventory items can be used for various purposes, including feeding batches.**

Create once. Use everywhere. One system. One source of truth.
