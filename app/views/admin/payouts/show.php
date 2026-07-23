<?php $pageTitle = 'Payout Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-receipt me-2"></i>Payout Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/payouts">Payouts</a></li>
                    <li class="breadcrumb-item active">#<?= $payout['id'] ?? 0 ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/payouts" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($payout)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-receipt fa-4x d-block mb-3"></i><h5>Payout not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payout Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Payout ID</div><div class="col-sm-7"><strong>#<?= $payout['id'] ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">User</div><div class="col-sm-7"><?= $payout['user_name'] ?? 'User #'.$payout['user_id'] ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Amount</div><div class="col-sm-7"><strong class="text-success">₹<?= number_format($payout['amount'] ?? 0, 2) ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Type</div><div class="col-sm-7"><?= $payout['type'] ?? 'Commission' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Status</div><div class="col-sm-7"><span class="badge bg-<?= ($payout['status'] ?? 'pending') === 'paid' ? 'success' : 'secondary' ?>-subtle text-<?= ($payout['status'] ?? 'pending') === 'paid' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($payout['status'] ?? 'Pending') ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Date</div><div class="col-sm-7"><?= date('d M Y H:i', strtotime($payout['created_at'] ?? 'now')) ?></div></div>
                    <div class="row"><div class="col-sm-5 text-muted">Description</div><div class="col-sm-7"><?= $payout['description'] ?? '-' ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Payment Method</div><div class="col-sm-7"><?= $payout['payment_method'] ?? 'Bank Transfer' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Transaction ID</div><div class="col-sm-7"><?= $payout['transaction_id'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Bank Account</div><div class="col-sm-7"><?= $payout['bank_account'] ?? '-' ?></div></div>
                    <div class="row"><div class="col-sm-5 text-muted">Paid Date</div><div class="col-sm-7"><?= $payout['paid_date'] ? date('d M Y', strtotime($payout['paid_date'])) : '-' ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
