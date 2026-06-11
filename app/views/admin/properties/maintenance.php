<?php $pageTitle = 'Property Maintenance'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-tools me-2"></i>Property Maintenance</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/properties">Properties</a></li>
                    <li class="breadcrumb-item active">Maintenance</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Requests</h6><h3 class="mb-0"><?= number_format($totalRequests ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Open</h6><h3 class="mb-0"><?= number_format($openRequests ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>In Progress</h6><h3 class="mb-0"><?= number_format($inProgressRequests ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Completed</h6><h3 class="mb-0"><?= number_format($completedRequests ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Maintenance Requests</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Property</th><th>Issue</th><th>Priority</th><th>Status</th><th>Reported By</th><th class="text-end pe-4">Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-tools fa-3x d-block mb-3"></i>No maintenance requests</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $i => $r): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $r['property_title'] ?? 'Property #'.$r['property_id'] ?></strong></td><td><?= $r['issue'] ?? '-' ?></td><td><span class="badge bg-<?= ($r['priority'] ?? 'medium') === 'high' ? 'danger' : (($r['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?>-subtle text-<?= ($r['priority'] ?? 'medium') === 'high' ? 'danger' : (($r['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?> rounded-pill px-3"><?= ucfirst($r['priority'] ?? 'Medium') ?></span></td><td><span class="badge bg-<?= ($r['status'] ?? 'open') === 'completed' ? 'success' : (($r['status'] ?? 'open') === 'in_progress' ? 'info' : 'warning') ?>-subtle text-<?= ($r['status'] ?? 'open') === 'completed' ? 'success' : (($r['status'] ?? 'open') === 'in_progress' ? 'info' : 'warning') ?> rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $r['status'] ?? 'Open')) ?></span></td><td><?= $r['reported_by'] ?? '-' ?></td><td class="text-end pe-4 small"><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
