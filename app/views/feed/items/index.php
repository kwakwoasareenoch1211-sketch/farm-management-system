<?php
$feedItems = $feedItems ?? [];
$base      = rtrim(BASE_URL, '/');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Feed Items</h2>
        <p class="text-muted mb-0">Manage feed items - these are used when recording feed usage</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/feed/items/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Feed Item</a>
        <a href="<?= $base ?>/feed" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Feed Records</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Feed Items</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Unit of Measure</th>
                        <th>Unit Cost</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($feedItems)): ?>
                        <?php foreach ($feedItems as $item): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($item['item_name'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= strtolower($item['category'] ?? '') === 'feed' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars(ucfirst($item['category'] ?? 'other')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['unit_of_measure'] ?? 'kg') ?></td>
                                <td class="fw-semibold">GHS <?= number_format((float)($item['unit_cost'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($item['notes'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= $base ?>/feed/items/edit?id=<?= (int)$item['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="<?= $base ?>/feed/items/delete?id=<?= (int)$item['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this feed item?')">Del</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-2">No feed items yet.</p>
                                <a href="<?= $base ?>/feed/items/create" class="btn btn-primary btn-sm">Add First Feed Item</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
