# Unified Stock Receipt → Feed Records Flow

## Current Problem
- Stock Receipt and Feed Records are separate
- User receives feed stock → then has to manually record feed usage
- Calculations done separately in different places
- Confusing workflow

## New Solution: Unified Flow

### Concept
When you receive feed stock → It automatically appears in feed records list → One calculation system

### Flow

```
1. Receive Feed Stock
   ↓
2. System creates stock receipt
   ↓
3. System AUTOMATICALLY creates feed record (available for use)
   ↓
4. Feed appears in feed list (ready to assign to batches)
   ↓
5. User assigns feed to batches from the list
   ↓
6. ONE calculation for everything (stock, cost, usage)
```

## Implementation

### Stock Receipt for Feed Items
When receiving stock for feed category items:
- Create stock receipt (inventory increases)
- Automatically create "available feed" record
- Link them together
- One calculation for cost and quantity

### Feed Records List
Shows:
- Available feed (from stock receipts, not yet assigned)
- Assigned feed (already given to batches)
- Status: Available / Assigned / Used

### Unified Calculations
```php
// ONE calculation point
$feedStock = StockReceipt (feed items)
$feedAvailable = $feedStock - $feedUsed
$feedCost = calculated once from stock receipt
$totalFeedValue = ONE source of truth
```

## Database Changes

### Add status to feed_records
```sql
ALTER TABLE feed_records 
ADD COLUMN status ENUM('available', 'assigned', 'used') DEFAULT 'available',
ADD COLUMN stock_receipt_id INT UNSIGNED,
ADD FOREIGN KEY (stock_receipt_id) REFERENCES stock_receipts(id);
```

### Link Structure
```
stock_receipts (feed items)
    ↓ (auto-creates)
feed_records (status='available', batch_id=NULL)
    ↓ (user assigns)
feed_records (status='assigned', batch_id=X)
    ↓ (birds consume)
feed_records (status='used')
```

## User Workflow

### Old Way (Confusing):
1. Receive feed stock → Inventory
2. Go to feed page
3. Manually create feed record
4. Select from inventory dropdown
5. Calculate quantity
6. Assign to batch
7. Stock deducted

### New Way (Simple):
1. Receive feed stock → Automatically in feed list
2. Go to feed page → See available feed
3. Click "Assign to Batch"
4. Select batch
5. Done! (calculations automatic)

## Views Update

### Stock Receipt Page
```php
// When receiving feed items
if ($item['category'] === 'feed') {
    echo "This will be automatically available in Feed Records";
    // Show link to feed page
}
```

### Feed Records Page
```php
// Show two sections
1. Available Feed (from stock receipts, not assigned)
   - Quantity available
   - Cost per kg
   - Action: Assign to Batch

2. Assigned/Used Feed (already given to batches)
   - Batch name
   - Quantity used
   - Date used
   - Cost
```

## Benefits

1. **No Duplication**: Receive once, use once
2. **One Calculation**: Cost and quantity calculated once
3. **Clear Status**: Available vs Assigned vs Used
4. **Automatic Link**: Stock receipt → Feed record (automatic)
5. **Simple Workflow**: Receive → Assign → Done

## Code Changes Needed

### 1. StockReceipt Model
```php
public function create(array $data): bool
{
    // ... existing code ...
    
    // If feed item, auto-create feed record
    if ($item['category'] === 'feed') {
        $this->createAvailableFeedRecord([
            'stock_receipt_id' => $receiptId,
            'inventory_item_id' => $itemId,
            'quantity_kg' => $quantity,
            'unit_cost' => $unitCost,
            'status' => 'available',
            'batch_id' => null, // Not assigned yet
        ]);
    }
}
```

### 2. Feed Model
```php
public function getAvailableFeed(): array
{
    // Get feed not yet assigned to batches
    return $this->db->query("
        SELECT * FROM feed_records 
        WHERE status = 'available' 
        AND batch_id IS NULL
    ")->fetchAll();
}

public function assignToBatch(int $feedId, int $batchId): bool
{
    // Assign available feed to a batch
    return $this->db->prepare("
        UPDATE feed_records 
        SET status = 'assigned', 
            batch_id = ?,
            record_date = NOW()
        WHERE id = ? AND status = 'available'
    ")->execute([$batchId, $feedId]);
}
```

### 3. Feed Controller
```php
public function index(): void
{
    $availableFeed = $this->feedModel->getAvailableFeed();
    $assignedFeed = $this->feedModel->getAssignedFeed();
    
    $this->view('feed/index', [
        'availableFeed' => $availableFeed,
        'assignedFeed' => $assignedFeed,
    ]);
}

public function assignToBatch(): void
{
    $feedId = $_POST['feed_id'];
    $batchId = $_POST['batch_id'];
    
    $this->feedModel->assignToBatch($feedId, $batchId);
    
    redirect('/feed');
}
```

## Status: Ready to Implement

This creates a unified system where:
- Stock receipts automatically create available feed
- Feed list shows what's available vs used
- One calculation for everything
- Simple workflow: Receive → Assign → Done
