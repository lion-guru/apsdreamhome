<?php $pageTitle = 'Payout Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-bar me-2"></i>Payout Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/payouts">Payouts</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ul>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm" style="width:auto"><option>This Year</option><option>This Quarter</option><option>This Month</option></select>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Payouts</h6><h3 class="mb-0"><?= number_format($totalPayouts ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Amount</h6><h3 class="mb-0">₹<?= number_format($totalAmount ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0">₹<?= number_format($pendingAmount ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Avg. Payout</h6><h3 class="mb-0">₹<?= number_format($avgPayout ?? 0, 2) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Payout Trends</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-line fa-4x d-block mb-3"></i>Monthly payout trend chart</div></div></div>
        <div class="col-md-4 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>By Type</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Distribution chart</div></div></div>
    </div>
</div>
