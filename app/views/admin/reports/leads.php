<?php $pageTitle = 'Leads Report'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-users me-2"></i>Leads Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Leads</li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Leads</h6><h3 class="mb-0"><?= number_format($totalLeads ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Converted</h6><h3 class="mb-0"><?= number_format($convertedLeads ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Conversion Rate</h6><h3 class="mb-0"><?= number_format($conversionRate ?? 0, 1) ?>%</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Avg. Score</h6><h3 class="mb-0"><?= number_format($avgScore ?? 0, 1) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Leads by Source</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-column fa-4x d-block mb-3"></i>Source breakdown chart</div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Lead Trend</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-line fa-4x d-block mb-3"></i>Monthly lead trend chart</div></div></div>
    </div>
</div>
