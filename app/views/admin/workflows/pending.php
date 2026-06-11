<?php $pageTitle = 'Pending Workflows'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-clock me-2"></i>Pending Workflows</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/workflows">Workflows</a></li>
                    <li class="breadcrumb-item active">Pending</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/workflows/list" class="btn btn-info btn-sm"><i class="fas fa-list me-1"></i>All Workflows</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0"><?= number_format($pendingCount ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-danger text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Overdue</h6><h3 class="mb-0"><?= number_format($overdueCount ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>In Progress</h6><h3 class="mb-0"><?= number_format($inProgressCount ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Pending Workflows</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Name</th><th>Type</th><th>Assigned To</th><th>Priority</th><th>Due Date</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($pendingWorkflows)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x d-block mb-3 text-success"></i>No pending workflows!</td></tr>
                        <?php else: ?>
                            <?php foreach ($pendingWorkflows as $i => $w): ?>
                            <tr><td class="ps-4"><?= $w['id'] ?? $i+1 ?></td><td><strong><?= $w['name'] ?></strong></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $w['type'] ?? 'General' ?></span></td><td><?= $w['assigned_to_name'] ?? 'Unassigned' ?></td><td><span class="badge bg-<?= ($w['priority'] ?? 'medium') === 'high' ? 'danger' : (($w['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?>-subtle text-<?= ($w['priority'] ?? 'medium') === 'high' ? 'danger' : (($w['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?> rounded-pill px-3"><?= ucfirst($w['priority'] ?? 'Medium') ?></span></td><td class="<?= (strtotime($w['due_date'] ?? '2099-01-01') < time() && $w['due_date']) ? 'text-danger fw-bold' : '' ?>"><?= $w['due_date'] ? date('d M Y', strtotime($w['due_date'])) : '-' ?></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-success" onclick="alert('Approve workflow #<?= $w['id'] ?>')"><i class="fas fa-check"></i></button> <button class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></button></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
