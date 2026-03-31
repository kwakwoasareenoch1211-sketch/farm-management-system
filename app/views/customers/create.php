<?php $farms = $farms ?? []; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Invalid farm selected. Please try again.
    </div>
<?php endif; ?>

<div class="sales-card p-4">
    <h2 class="fw-bold mb-3">Add Customer</h2>

    <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/customers/store">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Farm</label>
                <select name="farm_id" class="form-select" required>
                    <option value="">Select farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= (int)$farm['id'] ?>">
                            <?= htmlspecialchars($farm['farm_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Customer Type</label>
                <select name="customer_type" class="form-select" required>
                    <option value="individual">Individual</option>
                    <option value="retailer">Retailer</option>
                    <option value="wholesaler">Wholesaler</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="company">Company</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Address Line</label>
                <input type="text" name="address_line" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark">Save Customer</button>
            <a href="<?= rtrim(BASE_URL, '/') ?>/customers" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>