<?php $pageTitle = 'Plot Allocations'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-handshake me-2"></i>Plot Allocations</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/plots">Plots</a></li>
                    <li class="breadcrumb-item active">Allocations</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Allocations</h6><h3 class="mb-0"><?= number_format($totalAllocations ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0"><?= number_format($pendingAllocations ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Approved</h6><h3 class="mb-0"><?= number_format($approvedAllocations ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Area</h6><h3 class="mb-0"><?= number_format($totalArea ?? 0) ?> sqft</h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Allocation Records</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Plot</th><th>Customer</th><th>Allocation Date</th><th>Amount</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-handshake fa-3x d-block mb-3"></i>No allocations recorded</td></tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $i => $a): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $a['plot_number'] ?? 'Plot #'.$a['plot_id'] ?></strong></td><td><?= $a['customer_name'] ?? '-' ?></td><td><?= date('d M Y', strtotime($a['allocation_date'] ?? 'now')) ?></td><td class="fw-bold">₹<?= number_format($a['amount'] ?? 0, 2) ?></td><td><span class="badge bg-<?= ($a['status'] ?? 'pending') === 'approved' ? 'success' : (($a['status'] ?? 'pending') === 'pending' ? 'warning' : 'secondary') ?>-subtle text-<?= ($a['status'] ?? 'pending') === 'approved' ? 'success' : (($a['status'] ?? 'pending') === 'pending' ? 'warning' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($a['status'] ?? 'Pending') ?></span></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></button></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
