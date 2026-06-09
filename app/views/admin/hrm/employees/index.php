<?php
$page_title = $page_title ?? 'users - APS Dream Home';
$page_heading = $page_heading ?? 'users';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i><?= htmlspecialchars($page_heading ?? 'users') ?></h4>
        <a href="<?= BASE_URL ?>/admin/hrm/users/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= __('hr_add_employee') ?></a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <p class="text-muted"><?= __('hr_no_employees') ?></p>
        </div>
    </div>
</div>
