<?php $pageTitle = 'Property Allocations'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-handshake me-2"></i>Property Allocations</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/properties">Properties</a></li>
                    <li class="breadcrumb-item active">Allocations</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Allocations</h6><h3 class="mb-0"><?= number_format($totalAllocations ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0"><?= number_format($pendingAllocations ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Completed</h6><h3 class="mb-0"><?= number_format($completedAllocations ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Allocation Records</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Property</th><th>Customer</th><th>Date</th><th>Type</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-handshake fa-3x d-block mb-3"></i>No allocations yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $i => $a): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $a['property_title'] ?? 'Property #'.$a['property_id'] ?></strong></td><td><?= $a['customer_name'] ?? '-' ?></td><td><?= date('d M Y', strtotime($a['created_at'] ?? 'now')) ?></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $a['type'] ?? 'Sale' ?></span></td><td><span class="badge bg-<?= ($a['status'] ?? 'pending') === 'completed' ? 'success' : (($a['status'] ?? 'pending') === 'pending' ? 'warning' : 'secondary') ?>-subtle text-<?= ($a['status'] ?? 'pending') === 'completed' ? 'success' : (($a['status'] ?? 'pending') === 'pending' ? 'warning' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($a['status'] ?? 'Pending') ?></span></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></button></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
