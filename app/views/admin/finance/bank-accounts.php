<?php
$page_title = $page_title ?? 'Bank Accounts - APS Dream Home';
$page_heading = $page_heading ?? 'Bank Accounts';
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-cog me-2"></i><?= htmlspecialchars($page_heading ?? 'Bank Accounts') ?></h2>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-construction fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Bank Accounts</h4>
            <p class="text-muted">This module is under development. Check back soon.</p>
        </div>
    </div>
</div>
?>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/admin.php';
?>