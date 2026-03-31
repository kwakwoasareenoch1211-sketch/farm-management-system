# Poultry Dashboard - Business Structure

## Sections (In Order)

### 1. Header & Alerts
- Low stock warnings
- Critical alerts
- Quick action buttons

### 2. Hero Section
- Flock health status
- Key metrics snapshot

### 3. Core Poultry KPIs (8 cards)
- Total Birds
- Active Batches
- Total Eggs
- Total Mortality
- Feed Used
- Avg Weight
- Stock Value
- Low Stock Items

### 4. Health & Treatment Row (3 panels)
- Flock Health Status
- Vaccination Status
- Medication Activity

### 5. Feed Management Section (NEW - Separated)
**Title:** Feed Inventory & Usage
- Feed Stock Levels (items categorized as "feed")
- Feed Usage Records (from feed_records table)
- Low Feed Stock Alerts
- Feed Cost Analysis
- Quick Actions: Record Feed, Receive Feed Stock

### 6. Medical Supplies Section (NEW - Separated)
**Title:** Medical Supplies & Medication
- Medication Stock Levels (items categorized as "medication")
- Medication Usage Records
- Low Medication Stock Alerts
- Medication Cost Tracking
- Quick Actions: Record Medication, Receive Medical Stock

### 7. Other Inventory Section (NEW - Separated)
**Title:** Equipment & Other Supplies
- Equipment stock (items not feed/medication)
- General supplies
- Low stock alerts
- Quick Actions: Manage Items, Receive Stock

### 8. Stock Movement Summary
- This month receipts
- This month issues
- Net movement
- By category breakdown

### 9. Quick Operations Grid
- All major actions organized by category:
  - Poultry Operations (batches, eggs, mortality, weights)
  - Feed Operations (feed records, feed stock)
  - Health Operations (vaccination, medication)
  - Inventory Operations (items, receipts, issues)

### 10. Recent Activity
- Recent inventory movements
- Filtered by category tabs (All, Feed, Medication, Other)

### 11. Performance Summary
- Mortality Rate
- Avg FCR
- Avg Weight
- Feed Efficiency

## Business Logic

### Feed Items
- Category: "feed"
- Used in: feed_records table
- Tracked separately from other inventory
- Direct link to bird feeding operations
- Cost per kg tracked
- Usage tied to batches

### Medication Items
- Category: "medication"
- Used in: medication_records table
- Tracked separately from feed
- Cost per treatment tracked
- Usage tied to health events

### Other Items
- Category: "equipment", "supplies", etc.
- General farm inventory
- Not directly tied to birds
- Tracked for asset management

## Key Differentiators

1. **Feed Section**
   - Shows feed-specific items only
   - Links to feed usage records
   - Feed cost per batch
   - Feed conversion ratio

2. **Medication Section**
   - Shows medication items only
   - Links to medication records
   - Treatment costs
   - Health correlation

3. **Other Inventory**
   - Everything else
   - General supplies
   - Equipment
   - Miscellaneous

## Navigation Flow

```
Poultry Dashboard
├── Poultry Operations
│   ├── Batches
│   ├── Eggs
│   ├── Mortality
│   └── Weights
├── Feed Management
│   ├── Feed Stock (inventory items where category='feed')
│   ├── Feed Records (feed_records table)
│   └── Feed Analysis
├── Health Management
│   ├── Vaccination
│   ├── Medication Stock (inventory items where category='medication')
│   └── Medication Records (medication_records table)
└── General Inventory
    ├── All Items
    ├── Stock Receipts
    ├── Stock Issues
    └── Low Stock Alerts
```

## Data Separation

### Controller Level:
```php
// Separate items by category
$feedItems = array_filter($items, fn($i) => $i['category'] === 'feed');
$medicationItems = array_filter($items, fn($i) => $i['category'] === 'medication');
$otherItems = array_filter($items, fn($i) => !in_array($i['category'], ['feed', 'medication']));
```

### View Level:
```php
// Feed Section
foreach ($feedItems as $item) {
    // Show feed-specific data
}

// Medication Section
foreach ($medicationItems as $item) {
    // Show medication-specific data
}

// Other Section
foreach ($otherItems as $item) {
    // Show general inventory
}
```

## Benefits

1. **Clear Separation** - Feed, medication, and other items are distinct
2. **Business Logic** - Matches real farm operations
3. **Easy Navigation** - Find what you need quickly
4. **Better Insights** - Category-specific analytics
5. **Reduced Confusion** - No mixing of different item types
