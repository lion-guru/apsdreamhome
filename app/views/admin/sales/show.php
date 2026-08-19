<?php $pageTitle = 'Sale Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-invoice me-2"></i>Sale Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/sales">Sales</a></li>
                    <li class="breadcrumb-item active">#<?= $sale['id'] ?? 0 ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/sales/bookings/<?= $sale['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/sales" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($sale)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-file-invoice fa-4x d-block mb-3"></i><h5>Sale not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sale Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Sale ID</div><div class="col-sm-7"><strong>#<?= $sale['id'] ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Customer</div><div class="col-sm-7"><?= $sale['customer_name'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Property</div><div class="col-sm-7"><?= $sale['property_title'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Sale Price</div><div class="col-sm-7"><strong class="text-success">₹<?= number_format($sale['amount'] ?? 0, 2) ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Sale Date</div><div class="col-sm-7"><?= date('d M Y', strtotime($sale['sale_date'] ?? 'now')) ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Payment Mode</div><div class="col-sm-7"><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $sale['payment_mode'] ?? 'Cash')) ?></span></div></div>
                    <div class="row"><div class="col-sm-5 text-muted">Associate</div><div class="col-sm-7"><?= $sale['associate_name'] ?? '-' ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Additional Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Created By</div><div class="col-sm-7"><?= $sale['created_by_name'] ?? 'System' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-5 text-muted">Created At</div><div class="col-sm-7"><?= date('d M Y H:i', strtotime($sale['created_at'] ?? 'now')) ?></div></div>
                    <div class="row"><div class="col-sm-5 text-muted">Notes</div><div class="col-sm-7"><?= nl2br($sale['notes'] ?? '-') ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
