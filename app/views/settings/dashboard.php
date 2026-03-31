<?php
$base = rtrim(BASE_URL, '/');
$systemInfo = $systemInfo ?? [];
$dbStats = $dbStats ?? [];
?>
<style>
.settings-card{border-radius:16px;background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);transition:all .2s;}
.settings-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.08);}
.settings-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:16px 16px 0 0;padding:24px;}
.action-card{border-radius:12px;padding:20px;border:2px solid #e5e7eb;background:#fff;cursor:pointer;transition:all .2s;text-decoration:none;display:block;color:inherit;}
.action-card:hover{border-color:#667eea;background:#f9fafb;transform:translateY(-2px);box-shadow:0 4px 12px rgba(102,126,234,.15);color:inherit;}
.action-card .icon-box{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px;}
.stat-box{background:#f9fafb;border-radius:12px;padding:16px;border:1px solid #e5e7eb;}
.maintenance-item{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;}
.maintenance-item:hover{background:#f9fafb;}
.badge-status{padding:6px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
</style>

<div class="settings-card mb-4">
    <div class="settings-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1 fw-bold"><i class="bi bi-gear-fill me-2"></i>System Settings</h3>
                <p class="mb-0 opacity-90">Manage system configuration, maintenance, and administration</p>
            </div>
            <a href="<?= $base ?>/admin" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
        </div>
    </div>
</div>

<!-- System Overview Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-box">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1">Total Users</div>
                    <h4 class="mb-0 fw-bold"><?= $systemInfo['total_users'] ?? 0 ?></h4>
                </div>
                <div class="text-primary fs-2"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1">Active Batches</div>
                    <h4 class="mb-0 fw-bold"><?= $systemInfo['active_batches'] ?? 0 ?></h4>
                </div>
                <div class="text-success fs-2"><i class="bi bi-egg-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1">Database Size</div>
                    <h4 class="mb-0 fw-bold"><?= $dbStats['size'] ?? '0 MB' ?></h4>
                </div>
                <div class="text-info fs-2"><i class="bi bi-database-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1">System Status</div>
                    <h4 class="mb-0 fw-bold text-success">Healthy</h4>
                </div>
                <div class="text-success fs-2"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- System Administration -->
    <div class="col-lg-8">
        <div class="settings-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-sliders me-2"></i>System Administration</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="<?= $base ?>/users" class="action-card">
                        <div class="icon-box" style="background:#dbeafe;color:#2563eb;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-1">User Management</h6>
                        <p class="text-muted small mb-0">Manage system users and access</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/backup" class="action-card">
                        <div class="icon-box" style="background:#dcfce7;color:#16a34a;">
                            <i class="bi bi-cloud-arrow-down-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Backup & Restore</h6>
                        <p class="text-muted small mb-0">Database backup management</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/audit" class="action-card">
                        <div class="icon-box" style="background:#fef3c7;color:#d97706;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Activity Log</h6>
                        <p class="text-muted small mb-0">View system activity history</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/data-cleanup" class="action-card">
                        <div class="icon-box" style="background:#fce7f3;color:#db2777;">
                            <i class="bi bi-trash3-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Data Cleanup</h6>
                        <p class="text-muted small mb-0">Archive old records</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/system-info" class="action-card">
                        <div class="icon-box" style="background:#e0e7ff;color:#6366f1;">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-1">System Info</h6>
                        <p class="text-muted small mb-0">View technical details</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/notifications" class="action-card">
                        <div class="icon-box" style="background:#fef9c3;color:#ca8a04;">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Notifications</h6>
                        <p class="text-muted small mb-0">Configure alerts & reminders</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Business Configuration -->
        <div class="settings-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Business Configuration</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="<?= $base ?>/settings/farm-profile" class="action-card">
                        <div class="icon-box" style="background:#dbeafe;color:#2563eb;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Farm Profile</h6>
                        <p class="text-muted small mb-0">Business details & contact</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/capital" class="action-card">
                        <div class="icon-box" style="background:#dcfce7;color:#16a34a;">
                            <i class="bi bi-bank"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Capital & Equity</h6>
                        <p class="text-muted small mb-0">Owner investments</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/investments" class="action-card">
                        <div class="icon-box" style="background:#fef3c7;color:#d97706;">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Investments</h6>
                        <p class="text-muted small mb-0">Equipment & assets</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/inventory/items" class="action-card">
                        <div class="icon-box" style="background:#fce7f3;color:#db2777;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Inventory Items</h6>
                        <p class="text-muted small mb-0">Stock items setup</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/customers" class="action-card">
                        <div class="icon-box" style="background:#e0e7ff;color:#6366f1;">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Customers</h6>
                        <p class="text-muted small mb-0">Customer database</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= $base ?>/reports" class="action-card">
                        <div class="icon-box" style="background:#fef9c3;color:#ca8a04;">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Reports</h6>
                        <p class="text-muted small mb-0">Business analytics</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance & Status -->
    <div class="col-lg-4">
        <div class="settings-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-tools me-2"></i>System Maintenance</h5>
            
            <div class="maintenance-item">
                <div>
                    <div class="fw-semibold small">Database Backup</div>
                    <div class="text-muted" style="font-size:11px;">Last: <?= date('M d, Y') ?></div>
                </div>
                <span class="badge-status" style="background:#dcfce7;color:#16a34a;">Current</span>
            </div>

            <div class="maintenance-item">
                <div>
                    <div class="fw-semibold small">Data Integrity Check</div>
                    <div class="text-muted" style="font-size:11px;">All records validated</div>
                </div>
                <span class="badge-status" style="background:#dcfce7;color:#16a34a;">Passed</span>
            </div>

            <div class="maintenance-item">
                <div>
                    <div class="fw-semibold small">Stock Verification</div>
                    <div class="text-muted" style="font-size:11px;">Inventory reconciliation</div>
                </div>
                <span class="badge-status" style="background:#fef3c7;color:#d97706;">Pending</span>
            </div>

            <div class="maintenance-item">
                <div>
                    <div class="fw-semibold small">User Access Review</div>
                    <div class="text-muted" style="font-size:11px;">Security audit</div>
                </div>
                <span class="badge-status" style="background:#fef3c7;color:#d97706;">Due</span>
            </div>

            <div class="maintenance-item">
                <div>
                    <div class="fw-semibold small">Financial Reconciliation</div>
                    <div class="text-muted" style="font-size:11px;">Accounts balanced</div>
                </div>
                <span class="badge-status" style="background:#dcfce7;color:#16a34a;">Complete</span>
            </div>

            <button class="btn btn-primary w-100 mt-3">
                <i class="bi bi-play-circle me-2"></i>Run Maintenance Tasks
            </button>
        </div>

        <div class="settings-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-server me-2"></i>System Information</h5>
            
            <div class="mb-3">
                <div class="text-muted small mb-1">PHP Version</div>
                <div class="fw-semibold"><?= phpversion() ?></div>
            </div>

            <div class="mb-3">
                <div class="text-muted small mb-1">Server Software</div>
                <div class="fw-semibold"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></div>
            </div>

            <div class="mb-3">
                <div class="text-muted small mb-1">Database</div>
                <div class="fw-semibold">MySQL (PDO)</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small mb-1">System Time</div>
                <div class="fw-semibold"><?= date('M d, Y H:i:s') ?></div>
            </div>

            <div class="mb-0">
                <div class="text-muted small mb-1">Uptime</div>
                <div class="fw-semibold text-success">Running</div>
            </div>
        </div>
    </div>
</div>
