<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FarmApp') ?> — FarmApp</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        html, body { height: 100%; margin: 0; font-family: Arial, sans-serif; background: #f4f7fb; overflow: hidden; }

        .topbar {
            height: 60px; min-height: 60px;
            background: linear-gradient(90deg, #0f172a, #1e293b);
            color: #fff; display: flex; align-items: center;
            justify-content: space-between; padding: 0 24px;
            position: fixed; top: 0; left: 0; right: 0; z-index: 1100;
            box-shadow: 0 2px 12px rgba(15,23,42,.15);
        }

        .app-shell { display: flex; margin-top: 60px; height: calc(100vh - 60px); }

        .sidebar {
            width: 260px; min-width: 260px;
            background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
            color: #fff; padding: 20px 14px;
            overflow-y: auto; overflow-x: hidden;
            position: fixed; top: 60px; left: 0;
            height: calc(100vh - 60px);
        }

        .sidebar a {
            color: #d1d5db; text-decoration: none;
            display: flex; align-items: center; gap: 10px;
            padding: 10px 13px; border-radius: 10px; margin-bottom: 4px;
            transition: all .18s ease; font-size: 14px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,.09); color: #fff;
        }
        .sidebar-title {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: .08em; color: #6b7280;
            margin: 16px 12px 8px;
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            height: calc(100vh - 60px);
            overflow-y: auto; overflow-x: hidden;
            padding: 28px;
        }

        .card { border-radius: 16px !important; }
        .btn { border-radius: 999px !important; }
        .badge { border-radius: 999px !important; }

        .sidebar::-webkit-scrollbar, .main-content::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb, .main-content::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,.4); border-radius: 20px;
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="fw-bold fs-5 d-flex align-items-center gap-2">
        <i class="bi bi-egg-fried text-warning"></i> FarmApp
    </div>
    <div class="text-white-50 small"><?= htmlspecialchars($pageTitle ?? '') ?></div>
    <div class="d-flex align-items-center gap-3">
        <a href="<?= rtrim(BASE_URL, '/') ?>/admin" class="text-white-50 text-decoration-none small">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <?php if (!empty($_SESSION['user_name'])): ?>
        <span class="text-white-50 small"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <?php endif; ?>
        <a href="<?= rtrim(BASE_URL, '/') ?>/logout" class="text-white-50 text-decoration-none small" title="Sign out">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</header>

<div class="app-shell">
    <?php require BASE_PATH . 'app/views/partials/sidebar.php'; ?>
    <main class="main-content">
        <?php require $viewPath; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
