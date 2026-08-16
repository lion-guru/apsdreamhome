<?php
$colony = $colony ?? [];
$layouts = $layouts ?? [];
$colonyId = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-th text-primary me-2"></i>Layout Plans — <?= htmlspecialchars($colony['name'] ?? 'Colony #'.$colonyId) ?></h4>
            <small class="text-muted">Plot Subdivision & Layout Versions</small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/layouts/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Layout
            </a>
            <a href="<?= BASE_URL ?>/admin/colonies/show/<?= $colonyId ?>" class="btn btn-secondary btn-sm ms-1">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-3">
        <?php if (empty($layouts)): ?>
            <div class="col-12">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-body text-center py-5">
                        <i class="fas fa-th fa-3x text-muted mb-3 d-block"></i>
                        <h5>No layouts created yet</h5>
                        <p class="text-muted">Create your first layout plan to subdivide plots and assign dimensions.</p>
                        <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/layouts/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create First Layout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($layouts as $l): ?>
        <div class="col-md-6 col-lg-4">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-th me-2"></i>
                        <?= htmlspecialchars($l['layout_name'] ?? '—') ?>
                        <?php if (!empty($l['is_current'])): ?>
                            <span class="badge bg-success ms-2">Current</span>
                        <?php endif; ?>
                    </span>
                    <span class="badge bg-secondary">v<?= htmlspecialchars($l['version'] ?? '1') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm mb-3">
                        <tr><th class="text-muted">Type</th><td><?= htmlspecialchars(ucwords($l['layout_type'] ?? '—')) ?></td></tr>
                        <tr><th class="text-muted">Total Plots</th><td><?= (int)($l['total_plots'] ?? 0) ?></td></tr>
                        <tr><th class="text-muted">Total Area</th><td><?= number_format((float)($l['total_area_sqft'] ?? 0), 0) ?> sqft</td></tr>
                        <tr><th class="text-muted">Roads %</th><td><?= number_format((float)($l['road_area_pct'] ?? 0), 1) ?>%</td></tr>
                        <tr><th class="text-muted">Common %</th><td><?= number_format((float)($l['common_area_pct'] ?? 0), 1) ?>%</td></tr>
                        <tr><th class="text-muted">Approved</th><td><?= !empty($l['approval_date']) ? '✓ '.htmlspecialchars($l['approval_date'] ?? '') : '<span class="text-muted">Pending</span>' ?></td></tr>
                        <tr><th class="text-muted">Auth #</th><td><?= htmlspecialchars($l['approval_number'] ?? '—') ?></td></tr>
                    </table></div>
                    <?php if (!empty($l['notes'])): ?>
                        <p class="small text-muted"><?= nl2br(htmlspecialchars($l['notes'] ?? '')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
