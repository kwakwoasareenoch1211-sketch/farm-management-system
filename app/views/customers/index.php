<div class="sales-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Customers</h2>
            <p class="text-muted mb-0">Manage customer records used in sales and business reporting.</p>
        </div>
        <a href="<?= rtrim(BASE_URL, '/') ?>/customers/create" class="btn btn-dark">Add Customer</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Farm</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['customer_name']) ?></td>
                            <td><?= htmlspecialchars($customer['farm_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars(ucfirst($customer['customer_type'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($customer['address_line'] ?? '-') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= rtrim(BASE_URL, '/') ?>/customers/edit?id=<?= (int)$customer['id'] ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="<?= rtrim(BASE_URL, '/') ?>/customers/delete?id=<?= (int)$customer['id'] ?>" onclick="return confirm('Delete this customer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No customers yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>