<?php
$customer = $customer ?? [];
$farms = $farms ?? [];
$customerType = $customer['customer_type'] ?? 'individual';
?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Invalid farm selected. Please try again.
    </div>
<?php endif; ?>

<div class="sales-card p-4">
    <h2 class="fw-bold mb-3">Edit Customer</h2>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/customers/update">
        <input type="hidden" name="id" value="<?= (int)$customer['id'] ?>">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>" <?= (int)$farm['id'] === (int)($customer['farm_id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($farm['farm_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Customer Type</label>
                <select name="customer_type" class="form-select" required>
                    <option value="individual" <?= $customerType === 'individual' ? 'selected' : '' ?>>Individual</option>
                    <option value="retailer" <?= $customerType === 'retailer' ? 'selected' : '' ?>>Retailer</option>
                    <option value="wholesaler" <?= $customerType === 'wholesaler' ? 'selected' : '' ?>>Wholesaler</option>
                    <option value="restaurant" <?= $customerType === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
                    <option value="company" <?= $customerType === 'company' ? 'selected' : '' ?>>Company</option>
                    <option value="other" <?= $customerType === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Address Line</label>
                <input type="text" name="address_line" class="form-control" value="<?= htmlspecialchars($customer['address_line'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($customer['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Update Customer</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/customers" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>