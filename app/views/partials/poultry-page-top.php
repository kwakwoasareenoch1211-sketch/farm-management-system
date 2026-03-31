<?php
$addUrl = $addUrl ?? '#';
$addLabel = $addLabel ?? 'Add Record';
$pageTitle = $pageTitle ?? 'Poultry Page';
$pageDesc = $pageDesc ?? 'Manage records here.';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge rounded-pill text-bg-success mb-2 px-3 py-2">Poultry Work Area</span>
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($pageDesc) ?></p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= htmlspecialchars($addUrl) ?>" class="btn btn-dark">
            <i class="bi bi-plus-circle me-1"></i> <?= htmlspecialchars($addLabel) ?>
        </a>
    </div>
</div>

<style>
    .work-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .mini-stat {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #eef2f7;
        height: 100%;
    }

    .mini-stat .label {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .mini-stat .value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .mini-stat .meta {
        font-size: 12px;
        color: #94a3b8;
    }

    .soft-filter {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
    }

    .action-btns {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>