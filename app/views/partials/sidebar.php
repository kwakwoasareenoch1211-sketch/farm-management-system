<?php
$sidebarType = $sidebarType ?? 'admin';

function nav_url(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$projectFolder = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');

if ($projectFolder !== '' && str_starts_with($currentPath, $projectFolder)) {
    $currentPath = trim(substr($currentPath, strlen($projectFolder)), '/');
}

$menus = [
    'admin' => [
        ['label' => 'Admin Dashboard', 'icon' => 'bi-grid-1x2-fill', 'url' => nav_url('admin'), 'match' => 'admin'],
        ['label' => 'Poultry', 'icon' => 'bi-feather', 'url' => nav_url('poultry'), 'match' => 'poultry'],
        ['label' => 'Financial', 'icon' => 'bi-cash-stack', 'url' => nav_url('financial'), 'match' => 'financial'],
        ['label' => 'Economic', 'icon' => 'bi-graph-up-arrow', 'url' => nav_url('economic'), 'match' => 'economic'],
        ['label' => 'Reports', 'icon' => 'bi-file-earmark-bar-graph', 'url' => nav_url('reports'), 'match' => 'reports'],
        ['label' => 'Sales', 'icon' => 'bi-cart3', 'url' => nav_url('sales'), 'match' => 'sales'],
        ['label' => 'Users', 'icon' => 'bi-people', 'url' => nav_url('users'), 'match' => 'users'],
        ['label' => 'Settings', 'icon' => 'bi-gear', 'url' => nav_url('settings'), 'match' => 'settings'],
    ],

    'poultry' => [
        ['label' => 'Poultry Dashboard', 'icon' => 'bi-feather', 'url' => nav_url('poultry'), 'match' => 'poultry'],
        ['label' => 'Batches', 'icon' => 'bi-collection', 'url' => nav_url('batches'), 'match' => 'batches'],
        ['label' => 'Feed Records', 'icon' => 'bi-basket2', 'url' => nav_url('feed'), 'match' => 'feed'],
        ['label' => 'Mortality', 'icon' => 'bi-heart-pulse', 'url' => nav_url('mortality'), 'match' => 'mortality'],
        ['label' => 'Vaccination', 'icon' => 'bi-shield-check', 'url' => nav_url('vaccination'), 'match' => 'vaccination'],
        ['label' => 'Medication', 'icon' => 'bi-capsule-pill', 'url' => nav_url('medication'), 'match' => 'medication'],
        ['label' => 'Egg Production', 'icon' => 'bi-egg-fried', 'url' => nav_url('egg-production'), 'match' => 'egg-production'],
        ['label' => 'Weight Tracking', 'icon' => 'bi-speedometer2', 'url' => nav_url('weights'), 'match' => 'weights'],
        ['label' => 'Sales', 'icon' => 'bi-cart3', 'url' => nav_url('sales'), 'match' => 'sales'],
        ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
    ],

    'financial' => [
        ['label' => 'Financial Dashboard', 'icon' => 'bi-cash-stack',        'url' => nav_url('financial'),        'match' => 'financial'],
        ['label' => 'All Sales (Revenue)',  'icon' => 'bi-cart3',             'url' => nav_url('sales'),            'match' => 'sales'],
        ['label' => 'New Sale',             'icon' => 'bi-plus-circle',       'url' => nav_url('sales/create'),     'match' => 'sales/create'],
        ['label' => 'Customers',            'icon' => 'bi-people',            'url' => nav_url('customers'),        'match' => 'customers'],
        ['label' => 'Expenses',             'icon' => 'bi-wallet2',           'url' => nav_url('expenses'),         'match' => 'expenses'],
        ['label' => 'Add Expense',          'icon' => 'bi-plus-circle',       'url' => nav_url('expenses/create'),  'match' => 'expenses/create'],
        ['label' => 'Liabilities',          'icon' => 'bi-credit-card',       'url' => nav_url('liabilities'),      'match' => 'liabilities'],
        ['label' => 'Losses & Write-offs',  'icon' => 'bi-exclamation-triangle', 'url' => nav_url('losses'),        'match' => 'losses'],
        ['label' => 'Capital',              'icon' => 'bi-bank',              'url' => nav_url('capital'),          'match' => 'capital'],
        ['label' => 'Add Capital',          'icon' => 'bi-plus-circle',       'url' => nav_url('capital/create'),   'match' => 'capital/create'],
        ['label' => 'Investments',          'icon' => 'bi-graph-up',          'url' => nav_url('investments'),      'match' => 'investments'],
        ['label' => 'Add Investment',       'icon' => 'bi-plus-circle',       'url' => nav_url('investments/create'),'match' => 'investments/create'],
        ['label' => 'Profit & Loss',        'icon' => 'bi-file-earmark-text', 'url' => nav_url('profit-loss'),      'match' => 'profit-loss'],
        ['label' => 'Sales Report',         'icon' => 'bi-bar-chart',         'url' => nav_url('reports/sales'),    'match' => 'reports/sales'],
        ['label' => 'Expense Report',       'icon' => 'bi-receipt',           'url' => nav_url('reports/expenses'), 'match' => 'reports/expenses'],
        ['label' => 'P&L Report',           'icon' => 'bi-graph-up',          'url' => nav_url('reports/profit-loss'), 'match' => 'reports/profit-loss'],
        ['label' => 'Back to Admin',        'icon' => 'bi-arrow-left',        'url' => nav_url('admin'),            'match' => 'admin'],
    ],

    'economic' => [
        ['label' => 'Economic Dashboard', 'icon' => 'bi-graph-up-arrow', 'url' => nav_url('economic'), 'match' => 'economic'],
        ['label' => 'Business Health', 'icon' => 'bi-bar-chart-line', 'url' => nav_url('business-health'), 'match' => 'business-health'],
        ['label' => 'Going Concern', 'icon' => 'bi-activity', 'url' => nav_url('going-concern'), 'match' => 'going-concern'],
        ['label' => 'Decision Support', 'icon' => 'bi-lightbulb', 'url' => nav_url('decision-support'), 'match' => 'decision-support'],
        ['label' => 'Financial Dashboard', 'icon' => 'bi-cash-stack', 'url' => nav_url('financial'), 'match' => 'financial'],
        ['label' => 'Profit & Loss', 'icon' => 'bi-file-earmark-text', 'url' => nav_url('profit-loss'), 'match' => 'profit-loss'],
        ['label' => 'Sales Dashboard', 'icon' => 'bi-cart3', 'url' => nav_url('sales'), 'match' => 'sales'],
        ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
    ],

    'sales' => [
        ['label' => 'Sales Dashboard', 'icon' => 'bi-cart3', 'url' => nav_url('sales'), 'match' => 'sales'],
        ['label' => 'Add Sale', 'icon' => 'bi-plus-circle', 'url' => nav_url('sales/create'), 'match' => 'sales/create'],
        ['label' => 'Customers', 'icon' => 'bi-people', 'url' => nav_url('customers'), 'match' => 'customers'],
        ['label' => 'Financial Dashboard', 'icon' => 'bi-cash-stack', 'url' => nav_url('financial'), 'match' => 'financial'],
        ['label' => 'Profit & Loss', 'icon' => 'bi-file-earmark-text', 'url' => nav_url('profit-loss'), 'match' => 'profit-loss'],
        ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
    ],

    'users' => [
        ['label' => 'Users Dashboard', 'icon' => 'bi-people', 'url' => nav_url('users'), 'match' => 'users'],
        ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
    ],

    'settings' => [
        ['label' => 'Settings Dashboard', 'icon' => 'bi-gear', 'url' => nav_url('settings'), 'match' => 'settings'],
        ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
    ],



    'reports' => [
    ['label' => 'Reports Dashboard', 'icon' => 'bi-bar-chart', 'url' => nav_url('reports'), 'match' => 'reports'],
    ['label' => 'Batch Performance', 'icon' => 'bi-clipboard-data', 'url' => nav_url('reports/batch-performance'), 'match' => 'reports/batch-performance'],
    ['label' => 'Feed Report', 'icon' => 'bi-basket', 'url' => nav_url('reports/feed'), 'match' => 'reports/feed'],
    ['label' => 'Mortality Report', 'icon' => 'bi-heart-pulse', 'url' => nav_url('reports/mortality'), 'match' => 'reports/mortality'],
    ['label' => 'Vaccination Report', 'icon' => 'bi-shield-check', 'url' => nav_url('reports/vaccination'), 'match' => 'reports/vaccination'],
    ['label' => 'Medication Report', 'icon' => 'bi-capsule', 'url' => nav_url('reports/medication'), 'match' => 'reports/medication'],
    ['label' => 'Weight Report', 'icon' => 'bi-activity', 'url' => nav_url('reports/weight'), 'match' => 'reports/weight'],
    ['label' => 'Egg Production Report', 'icon' => 'bi-egg', 'url' => nav_url('reports/egg-production'), 'match' => 'reports/egg-production'],
    ['label' => 'Sales Report', 'icon' => 'bi-cart', 'url' => nav_url('reports/sales'), 'match' => 'reports/sales'],
    ['label' => 'Expense Report', 'icon' => 'bi-receipt', 'url' => nav_url('reports/expenses'), 'match' => 'reports/expenses'],
    ['label' => 'Profit & Loss', 'icon' => 'bi-graph-up', 'url' => nav_url('reports/profit-loss'), 'match' => 'reports/profit-loss'],
    ['label' => 'Forecast Report', 'icon' => 'bi-graph-up-arrow', 'url' => nav_url('reports/forecast'), 'match' => 'reports/forecast'],
    ['label' => 'Business Health', 'icon' => 'bi-heart', 'url' => nav_url('reports/business-health'), 'match' => 'reports/business-health'],
    ['label' => 'Decision Recommendations', 'icon' => 'bi-lightbulb', 'url' => nav_url('reports/decisions'), 'match' => 'reports/decisions'],
    ['label' => 'Custom Reports', 'icon' => 'bi-file-earmark-text', 'url' => nav_url('reports/custom'), 'match' => 'reports/custom'],
    ['label' => 'Export Center', 'icon' => 'bi-download', 'url' => nav_url('reports/export'), 'match' => 'reports/export'],
    ['label' => 'Back to Admin', 'icon' => 'bi-arrow-left', 'url' => nav_url('admin'), 'match' => 'admin'],
],



];

$items = $menus[$sidebarType] ?? $menus['admin'];
?>

<aside class="sidebar">
    <div class="fw-bold fs-5 mb-3">FarmApp</div>
    <div class="sidebar-title"><?= htmlspecialchars(ucfirst($sidebarType)) ?> Menu</div>

    <?php foreach ($items as $item): ?>
        <?php
        $active = ($currentPath === $item['match'] || str_starts_with($currentPath, $item['match'] . '/')) ? 'active' : '';
        if ($currentPath === '' && $item['match'] === 'admin') {
            $active = 'active';
        }
        ?>
        <a href="<?= htmlspecialchars($item['url']) ?>" class="<?= $active ?>">
            <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
            <span><?= htmlspecialchars($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</aside>