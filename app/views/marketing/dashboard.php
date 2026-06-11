<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Marketing Dashboard') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/marketing/campaigns" class="btn btn-primary btn-sm"><i class="fas fa-bullhorn me-1"></i>Campaigns</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-bullhorn fa-2x text-primary"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Active Campaigns</h6><h3 class="mb-0"><?= (int)($stats['active_campaigns'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-users fa-2x text-success"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Total Leads</h6><h3 class="mb-0"><?= (int)($stats['total_leads'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-percentage fa-2x text-warning"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Conversion Rate</h6><h3 class="mb-0"><?= round((float)($stats['conversion_rate'] ?? 0), 1) ?>%</h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-rupee-sign fa-2x text-info"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Total Spent</h6><h3 class="mb-0">₹<?= number_format((int)($stats['total_spent'] ?? 0)) ?></h3></div></div></div></div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Lead Trends</h5></div>
                <div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-area fa-4x d-block mb-3"></i>Lead trend chart will render here</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Campaign Performance</h5></div>
                <div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Campaign performance chart will render here</div>
            </div>
        </div>
    </div>
</div>
