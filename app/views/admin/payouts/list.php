ï»¿<?php $pageTitle = 'Payout List'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-money-bill-wave me-2"></i>Payout List</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/payouts">Payouts</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/payouts/analytics" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i>Analytics</a>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Payouts</h5></div>
                <div class="col-auto">
                    <form class="d-flex" method="GET"><input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search..." class="style-47085"><button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-search"></i></button></form>
    <?php echo CSRFProtection::csrfField(); ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>User</th><th>Amount</th><th>Type</th><th>Status</th><th>Date</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($payouts)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-money-bill-wave fa-3x d-block mb-3"></i>No payouts found</td></tr>
                        <?php else: ?>
                            <?php foreach ($payouts as $i => $p): ?>
                            <tr><td class="ps-4"><?= $p['id'] ?? $i+1 ?></td><td><strong><?= $p['user_name'] ?? 'User #'.$p['user_id'] ?></strong></td><td class="fw-bold">₹<?= number_format($p['amount'] ?? 0, 2) ?></td><td><?= $p['type'] ?? 'Commission' ?></td><td><span class="badge bg-<?= ($p['status'] ?? 'pending') === 'paid' ? 'success' : (($p['status'] ?? 'pending') === 'processing' ? 'warning' : 'secondary') ?>-subtle text-<?= ($p['status'] ?? 'pending') === 'paid' ? 'success' : (($p['status'] ?? 'pending') === 'processing' ? 'warning' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($p['status'] ?? 'Pending') ?></span></td><td><?= date('d M Y', strtotime($p['created_at'] ?? 'now')) ?></td><td class="text-end pe-4"><a href="<?= BASE_URL ?>/admin/payouts/show/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
