# Apply Category Separation to Poultry Dashboard

## Instructions

Replace the "Inventory Stock Management" section (starting at line 182) in `app/views/poultry/dashboard.php` with the new category-separated sections below.

## What to Replace

**Find this section (lines 182-230):**
```php
<!-- Inventory Stock Management -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="pou-card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-success me-2"></i>Stock Movement (This Month)</h6>
            ...
        </div>
    </div>
    ...
</div>
```

## Replace With

### 1. Feed Management Section
See file: `poultry_sections_feed.txt`

### 2. Medical Supplies Section  
See file: `poultry_sections_medication.txt`

### 3. Other Inventory Section (if needed)
```php
<?php if (!empty($otherItems)): ?>
<div class="pou-card p-4 mb-4" style="border-left:4px solid #64748b;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-box-seam text-secondary me-2"></i>Equipment & Other Supplies</h5>
            <p class="text-muted small mb-0">General farm inventory and equipment</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/inventory/items/create" class="btn btn-secondary btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Item</a>
            <a href="<?= $base ?>/inventory/receipts/create" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock</a>
        </div>
    </div>

    <div class="alert alert-info mb-3">
        <strong><i class="bi bi-info-circle me-2"></i><?= count($otherItems) ?> other item(s) below reorder level</strong>
    </div>
    
    <div class="row g-2">
        <?php foreach ($otherItems as $item): ?>
            <div class="col-md-4">
                <div class="pou-soft">
                    <div class="fw-semibold small"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                    <div class="small text-muted">
                        Category: <?= htmlspecialchars(ucfirst($item['category'] ?? 'general')) ?> | 
                        Stock: <span class="text-warning fw-bold"><?= number_format((float)($item['current_stock'] ?? 0), 2) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-3">
        <a href="<?= $base ?>/inventory/items" class="btn btn-outline-secondary btn-sm">View All Inventory Items</a>
    </div>
</div>
<?php endif; ?>
```

## Also Update Recent Inventory Activity Table

Find the table (around line 240) and add category column:

```php
<thead class="table-light">
    <tr>
        <th>Date</th>
        <th>Item</th>
        <th>Category</th>  <!-- ADD THIS -->
        <th>Type</th>
        <th>Quantity</th>
        <th>Reference</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($recentInventoryActivities as $row): ?>
        <tr>
            <td class="small"><?= htmlspecialchars(date('M d', strtotime($row['activity_date'] ?? ''))) ?></td>
            <td class="small fw-semibold"><?= htmlspecialchars($row['item_name'] ?? '-') ?></td>
            <!-- ADD THIS CATEGORY CELL -->
            <td>
                <?php
                $cat = strtolower($row['category'] ?? 'other');
                $catBadge = $cat === 'feed' ? 'success' : ($cat === 'medication' ? 'primary' : 'secondary');
                ?>
                <span class="badge bg-<?= $catBadge ?> badge-sm"><?= htmlspecialchars(ucfirst($cat)) ?></span>
            </td>
            <td>
                <?php
                $type = $row['movement_type'] ?? '';
                $badge = $type === 'receipt' ? 'success' : 'danger';
                ?>
                <span class="badge bg-<?= $badge ?> badge-sm"><?= htmlspecialchars(ucfirst($type)) ?></span>
            </td>
            <td class="small"><?= number_format((float)($row['quantity'] ?? 0), 2) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($row['reference_no'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>
```

## Steps to Apply

1. **Backup** current `app/views/poultry/dashboard.php`
2. **Open** the file in editor
3. **Find** line 182 (<!-- Inventory Stock Management -->)
4. **Delete** lines 182-230 (the old inventory section)
5. **Insert** the three new sections (Feed, Medication, Other)
6. **Find** the Recent Inventory Activity table
7. **Add** the Category column as shown above
8. **Save** the file
9. **Test** the dashboard

## Expected Result

The dashboard will now show:
- ✅ Feed Management section (green border)
- ✅ Medical Supplies section (purple border)
- ✅ Equipment & Other section (gray border, only if items exist)
- ✅ Category badges in activity table (color-coded)

## Verification

Check that:
- [ ] Feed items show only in Feed section
- [ ] Medication items show only in Medication section
- [ ] Other items show only in Other section (if any)
- [ ] Category column shows in activity table
- [ ] Color coding is correct (green/purple/gray)
- [ ] All links work correctly
- [ ] No PHP errors

## Status

Controller changes: ✅ DONE
View sections created: ✅ DONE
Ready to apply: ✅ YES

Just copy the sections from the .txt files and paste them into the dashboard!
