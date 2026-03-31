<div class="card admin-panel">
    <div class="card-body">
        <div class="panel-head mb-4">
            <div>
                <h4 class="mb-1">Add User</h4>
                <p class="text-muted mb-0">Create a new system user account.</p>
            </div>
            <span class="soft-badge soft-badge-success">New User</span>
        </div>

        <form method="POST" action="<?= url('users/store') ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff" selected>Staff</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">Save User</button>
                <a href="<?= url('users') ?>" class="btn btn-light border">Back</a>
            </div>
        </form>
    </div>
</div>