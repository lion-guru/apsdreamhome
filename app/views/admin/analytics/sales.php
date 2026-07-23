<?php $pageTitle = 'Sales Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-simple me-2"></i>Sales Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/analytics">Analytics</a></li>
                    <li class="breadcrumb-item active">Sales</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Sales</h6><h3 class="mb-0"><?= number_format($totalSales ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Revenue</h6><h3 class="mb-0">₹<?= number_format($revenue ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Conversion Rate</h6><h3 class="mb-0"><?= number_format($conversionRate ?? 0, 1) ?>%</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Avg. Deal Size</h6><h3 class="mb-0">₹<?= number_format($avgDealSize ?? 0, 2) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Trend</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-line fa-4x d-block mb-3"></i>Sales trend chart area</div></div></div>
        <div class="col-md-4 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>By Category</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Category chart area</div></div></div>
    </div>
</div>
