<?php require_once BASE_PATH . 'app/views/partials/navbar.php'; ?>
<?php require_once BASE_PATH . 'app/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil-square me-2"></i>Edit User</h2>
            <a href="<?= rtrim(BASE_URL, '/') ?>/users" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Users
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/users/update">
                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="6">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                   <?= $user['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Update User
                        </button>
                        <a href="<?= rtrim(BASE_URL, '/') ?>/users" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'app/views/partials/footer.php'; ?>
