<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FarmApp') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
<style>
    html, body {
        height: 100%;
        margin: 0;
        overflow: hidden;
        font-family: Arial, sans-serif;
        background: #f4f7fb;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    .topbar {
        height: 68px;
        min-height: 68px;
        background: linear-gradient(90deg, #0f172a, #1e293b);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1100;
    }

    .app-shell {
        display: flex;
        margin-top: 68px;
        height: calc(100vh - 68px);
        overflow: hidden;
    }

    .sidebar {
        width: 280px;
        min-width: 280px;
        height: calc(100vh - 68px);
        background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
        color: #fff;
        padding: 24px 16px;
        box-shadow: 8px 0 24px rgba(15, 23, 42, 0.06);
        position: fixed;
        top: 68px;
        left: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .sidebar a {
        color: #d1d5db;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 12px;
        margin-bottom: 6px;
        transition: all 0.2s ease;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: rgba(255,255,255,0.08);
        color: #fff;
        transform: translateX(2px);
    }

    .sidebar-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        margin: 18px 12px 10px;
    }

    .main-content {
        margin-left: 280px;
        width: calc(100% - 280px);
        height: calc(100vh - 68px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 28px;
    }

    .dashboard-card,
    .metric-card {
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .metric-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .card {
        overflow: hidden;
    }

    .btn,
    .badge {
        border-radius: 999px;
    }

    .sidebar::-webkit-scrollbar,
    .main-content::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar::-webkit-scrollbar-thumb,
    .main-content::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.5);
        border-radius: 20px;
    }

    .sidebar::-webkit-scrollbar-track,
    .main-content::-webkit-scrollbar-track {
        background: transparent;
    }

    @media (max-width: 991.98px) {
        html, body {
            overflow: auto;
        }

        .topbar {
            position: sticky;
        }

        .app-shell {
            margin-top: 68px;
            height: auto;
            display: block;
        }

        .sidebar {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            min-width: 100%;
            height: auto;
        }

        .main-content {
            margin-left: 0;
            width: 100%;
            height: auto;
            overflow: visible;
        }
    }
</style>

</head>
<body>
<header class="topbar">
    <div class="fw-bold">FarmApp</div>
    <div><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
</header>