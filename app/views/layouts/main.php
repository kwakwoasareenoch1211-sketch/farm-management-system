<?php require BASE_PATH . 'app/views/partials/navbar.php'; ?>
<div class="app-shell">
    <?php require BASE_PATH . 'app/views/partials/sidebar.php'; ?>
    <main class="main-content">
        <?php require $viewFile; ?>
    </main>
</div>
<?php require BASE_PATH . 'app/views/partials/footer.php'; ?>