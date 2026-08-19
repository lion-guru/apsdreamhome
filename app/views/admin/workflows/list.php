<?php $pageTitle = 'Workflow List'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-list me-2"></i>Workflow List</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/workflows">Workflows</a></li>
                    <li class="breadcrumb-item active">All Workflows</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/workflows/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Workflow</a>
                <a href="<?= BASE_URL ?>/admin/workflows/pending" class="btn btn-warning btn-sm"><i class="fas fa-clock me-1"></i>Pending</a>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-diagram-project me-2"></i>All Workflows</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Name</th><th>Type</th><th>Assigned To</th><th>Priority</th><th>Status</th><th>Due Date</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($workflows)): ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-diagram-project fa-3x d-block mb-3"></i>No workflows created</td></tr>
                        <?php else: ?>
                            <?php foreach ($workflows as $i => $w): ?>
                            <tr><td class="ps-4"><?= $w['id'] ?? $i+1 ?></td><td><strong><?= $w['name'] ?></strong></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $w['type'] ?? 'General' ?></span></td><td><?= $w['assigned_to_name'] ?? 'Unassigned' ?></td><td><span class="badge bg-<?= ($w['priority'] ?? 'medium') === 'high' ? 'danger' : (($w['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?>-subtle text-<?= ($w['priority'] ?? 'medium') === 'high' ? 'danger' : (($w['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?> rounded-pill px-3"><?= ucfirst($w['priority'] ?? 'Medium') ?></span></td><td><span class="badge bg-<?= ($w['status'] ?? 'pending') === 'completed' ? 'success' : (($w['status'] ?? 'pending') === 'in_progress' ? 'info' : 'warning') ?>-subtle text-<?= ($w['status'] ?? 'pending') === 'completed' ? 'success' : (($w['status'] ?? 'pending') === 'in_progress' ? 'info' : 'warning') ?> rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $w['status'] ?? 'Pending')) ?></span></td><td><?= $w['due_date'] ? date('d M Y', strtotime($w['due_date'])) : '-' ?></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-info" aria-label="View"><i class="fas fa-eye"></i></button> <a href="<?= BASE_URL ?>/admin/workflows/<?= $w['id'] ?>/steps" class="btn btn-sm btn-outline-primary"><i class="fas fa-list-check"></i></a></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
