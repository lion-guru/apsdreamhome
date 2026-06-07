<?php
$colony = $colony ?? [];
$colonyId = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Add Development Cost — <?= htmlspecialchars($colony['name'] ?? 'Colony #'.$colonyId) ?></h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/costs" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <p class="text-muted">Use the form on the main Development Costs page to add a new cost entry.</p>
            <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/costs" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i>Go to Form
            </a>
        </div>
    </div>
</div>
