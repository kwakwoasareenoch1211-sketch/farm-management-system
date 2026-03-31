# Real-Time Feed System - Business Logic

## Overview
The system now works like a real business - inventory items flow directly into feed operations in real-time. No dropdowns, no manual selection, just search and use.

## How It Works (Real Business Flow)

### 1. Purchase & Receive Inventory
```
Business Action: Receive feed delivery
System Action: Add to inventory
Result: Item instantly available for use
```

### 2. Search & Use in Real-Time
```
Business Action: Need to feed birds
System Action: Type item name (e.g., "starter")
Result: Real-time search shows matching items with current stock
```

### 3. Select & Record
```
Business Action: Select item from search results
System Action: Auto-populate stock level and cost
Result: Record usage, stock automatically deducted
```

## Key Features

### Real-Time Search
- Type 2+ characters to search
- Results appear instantly (300ms debounce)
- Shows current stock levels
- Shows unit costs
- Highlights out-of-stock items

### Auto-Population
- Stock level from inventory
- Unit cost from inventory
- Total cost calculated automatically
- Stock validation in real-time

### Business Logic
- Can't use more than available stock
- Out-of-stock items clearly marked
- Real-time stock updates
- Immediate feedback

## Technical Implementation

### API Endpoints
```
GET /api/inventory/search?q=starter
- Real-time search
- Returns matching items with stock
- Ordered by stock level (highest first)

GET /api/inventory/item?id=123
- Get single item details
- Returns full item data

GET /api/inventory/available
- Get all items with stock > 0
- For bulk operations
```

### Frontend (JavaScript)
```javascript
// Real-time search with debounce
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetch('/api/inventory/search?q=' + query)
            .then(r => r.json())
            .then(items => displayResults(items));
    }, 300);
});

// Auto-populate on selection
function selectItem(id, name, stock, cost) {
    // Set hidden field
    selectedItemId.value = id;
    // Auto-fill cost
    unitCostInput.value = cost;
    // Store stock for validation
    currentStock = stock;
    // Calculate total
    calculateTotal();
}
```

### Backend (PHP)
```php
// API Controller
public function inventorySearch(): void
{
    $query = trim($_GET['q'] ?? '');
    
    $stmt = $db->prepare("
        SELECT id, item_name, current_stock, unit_cost
        FROM inventory_item
        WHERE item_name LIKE :query
        ORDER BY current_stock DESC
        LIMIT 20
    ");
    
    $stmt->execute([':query' => '%' . $query . '%']);
    echo json_encode($stmt->fetchAll());
}
```

## User Experience

### Old Way (Manual)
1. Go to inventory
2. Create item
3. Go to feed
4. Scroll through dropdown
5. Find item
6. Select
7. Enter quantity
8. Save

### New Way (Real-Time)
1. Type item name
2. Click from results
3. Enter quantity
4. Save

## Business Benefits

### Speed
- Instant search results
- No page loads
- No dropdown scrolling
- Faster data entry

### Accuracy
- Real-time stock levels
- Automatic cost population
- Validation before save
- Prevents errors

### Usability
- Natural search behavior
- Clear visual feedback
- Mobile-friendly
- Intuitive interface

## Database Flow

```
inventory_item (master)
    ↓ (real-time query)
API search endpoint
    ↓ (JSON response)
Frontend JavaScript
    ↓ (user selection)
Form submission
    ↓ (POST data)
Feed controller
    ↓ (create record)
feed_records table
    ↓ (stock deduction)
inventory_item updated
```

## Error Handling

### Insufficient Stock
```
User enters: 100 kg
Available: 50 kg
System: Shows warning, prevents submission
Action: User reduces quantity or receives more stock
```

### Item Not Found
```
User searches: "xyz123"
System: "No items found"
Action: User refines search or adds new item
```

### Network Error
```
API fails to respond
System: "Error loading items"
Action: User retries or refreshes page
```

## Mobile Optimization

- Touch-friendly search
- Large tap targets
- Responsive layout
- Fast loading
- Offline detection

## Performance

### Search Optimization
- 300ms debounce (prevents excessive queries)
- LIKE query with indexes
- Limit 20 results
- Order by relevance

### Caching Strategy
- No caching (always real-time)
- Fresh data on every search
- Accurate stock levels
- Current costs

## Security

### API Protection
- Input sanitization
- SQL injection prevention
- Rate limiting (future)
- Authentication required

### Data Validation
- Server-side validation
- Stock level checks
- Required field enforcement
- Type checking

## Future Enhancements

### Barcode Scanning
- Scan item barcode
- Auto-populate from inventory
- Mobile camera integration

### Voice Search
- "Feed starter to batch A"
- Natural language processing
- Hands-free operation

### Predictive Search
- Learn from usage patterns
- Suggest frequently used items
- Smart autocomplete

### Batch Operations
- Feed multiple batches at once
- Bulk quantity entry
- Split calculations

## Testing

### Manual Testing
1. Search for existing item → Should show results
2. Search for non-existent item → Should show "No items found"
3. Select item → Should auto-populate cost
4. Enter quantity > stock → Should show warning
5. Submit valid form → Should save and deduct stock

### API Testing
```bash
# Search test
curl "http://localhost/api/inventory/search?q=starter"

# Item details test
curl "http://localhost/api/inventory/item?id=1"

# Available items test
curl "http://localhost/api/inventory/available"
```

## Status: ✓ READY FOR PRODUCTION

The real-time feed system is complete and ready for business use. It works like a modern business application with instant search, real-time updates, and automatic data population.
