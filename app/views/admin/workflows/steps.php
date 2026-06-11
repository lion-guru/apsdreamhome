<?php $pageTitle = 'Workflow Steps'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-list-check me-2"></i>Workflow Steps</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/workflows">Workflows</a></li>
                    <li class="breadcrumb-item"><a href="/admin/workflows/list">All Workflows</a></li>
                    <li class="breadcrumb-item active">Steps: <?= $workflow['name'] ?? 'Workflow' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/workflows/list" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($workflow)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-diagram-project fa-4x d-block mb-3"></i><h5>Workflow not found</h5></div>
    <?php else: ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-diagram-project me-2"></i><?= $workflow['name'] ?> <small class="text-muted">(<?= $workflow['type'] ?? 'General' ?>)</small></h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-4">
                <?php if (empty($steps)): ?>
                    <div class="col-12 text-center py-4 text-muted"><i class="fas fa-list-check fa-3x d-block mb-3"></i>No steps defined for this workflow</div>
                <?php else: ?>
                    <?php foreach ($steps as $i => $step): ?>
                    <div class="col-md-6">
                        <div class="card border-start border-3 border-<?= ($step['status'] ?? 'pending') === 'completed' ? 'success' : (($step['status'] ?? 'pending') === 'in_progress' ? 'primary' : 'secondary') ?> shadow-sm h-100">
                            <div class="card-body aps-cp-card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= $i+1 ?>. <?= $step['title'] ?? 'Step '.($i+1) ?></h6>
                                        <p class="text-muted small mb-0"><?= $step['description'] ?? '' ?></p>
                                    </div>
                                    <span class="badge bg-<?= ($step['status'] ?? 'pending') === 'completed' ? 'success' : (($step['status'] ?? 'pending') === 'in_progress' ? 'primary' : 'secondary') ?>-subtle text-<?= ($step['status'] ?? 'pending') === 'completed' ? 'success' : (($step['status'] ?? 'pending') === 'in_progress' ? 'primary' : 'secondary') ?> rounded-pill"><?= ucfirst(str_replace('_', ' ', $step['status'] ?? 'Pending')) ?></span>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <i class="fas fa-user me-1"></i><?= $step['assigned_to_name'] ?? 'Unassigned' ?>
                                    <?php if ($step['due_date']): ?> | <i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($step['due_date'])) ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
