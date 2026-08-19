<?php $pageTitle = 'Property Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-building me-2"></i>Property Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/analytics">Analytics</a></li>
                    <li class="breadcrumb-item active">Property</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Properties</h6><h3 class="mb-0"><?= number_format($totalProperties ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Available</h6><h3 class="mb-0"><?= number_format($availableProperties ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Sold This Month</h6><h3 class="mb-0"><?= number_format($soldThisMonth ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Avg. Price</h6><h3 class="mb-0">₹<?= number_format($avgPrice ?? 0, 2) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Properties by Type</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-column fa-4x d-block mb-3"></i>Property type chart area</div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Monthly Listings</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-line fa-4x d-block mb-3"></i>Monthly trend chart area</div></div></div>
    </div>
</div>
