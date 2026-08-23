<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tasks me-2"></i><?= ($page_title ?? 'Construction Workflow') ?></h4>
        <a href="<?= ($base ?? BASE_URL) ?>saas/tools" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Tools</a>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach (($workflows ?? []) as $wf): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0"><?= htmlspecialchars($wf['name'] ?? '') ?></h6>
                        <span class="badge bg-<?= ($wf['status'] ?? 'pending') === 'completed' ? 'success' : (($wf['status'] ?? '') === 'in_progress' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($wf['status'] ?? 'pending') ?>
                        </span>
                    </div>
                    <div class="progress style-32124">
                        <div class="progress-bar" role="progressbar" class="style-62679" aria-valuenow="<?= ($wf['progress'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted mt-2 d-block"><?= ($wf['progress'] ?? 0) ?>% complete</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($workflows ?? [])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-tasks fa-3x mb-3"></i>
        <p>No workflow steps found. Start a new construction project to begin.</p>
        <a href="<?= ($base ?? BASE_URL) ?>saas/tools/inventory" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create Workflow</a>
    </div>
    <?php endif; ?>
</div>
