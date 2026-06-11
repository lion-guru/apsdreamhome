<?php $pageTitle = 'Reports Dashboard'; ?>
<?php $stats = $stats ?? ['total_reports' => 0, 'scheduled' => 0, 'sales' => 0, 'properties' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports Dashboard</h4>
        <a href="<?= BASE_URL ?>reports/generate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Generate Report</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Total Reports</small><h3 class="mb-0 mt-1"><?= $stats['total_reports'] ?? 0 ?></h3></div><div class="bg-primary-subtle p-3 rounded"><i class="fas fa-file-alt fa-2x text-primary"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Scheduled</small><h3 class="mb-0 mt-1"><?= $stats['scheduled'] ?? 0 ?></h3></div><div class="bg-warning-subtle p-3 rounded"><i class="fas fa-clock fa-2x text-warning"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Sales Reports</small><h3 class="mb-0 mt-1"><?= $stats['sales'] ?? 0 ?></h3></div><div class="bg-success-subtle p-3 rounded"><i class="fas fa-chart-line fa-2x text-success"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Property Reports</small><h3 class="mb-0 mt-1"><?= $stats['properties'] ?? 0 ?></h3></div><div class="bg-info-subtle p-3 rounded"><i class="fas fa-building fa-2x text-info"></i></div></div></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/sales" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-chart-line fa-3x text-success mb-3"></i><h6>Sales Report</h6><p class="small text-muted mb-0">Revenue, conversions, and sales trends</p></div></div></a></div>
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/properties" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-building fa-3x text-info mb-3"></i><h6>Properties Report</h6><p class="small text-muted mb-0">Property listings, status, and inventory</p></div></div></a></div>
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/financial" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-rupee-sign fa-3x text-warning mb-3"></i><h6>Financial Report</h6><p class="small text-muted mb-0">Income, expenses, and P&L</p></div></div></a></div>
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/user-activity" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-users fa-3x text-primary mb-3"></i><h6>User Activity</h6><p class="small text-muted mb-0">User registrations and login activity</p></div></div></a></div>
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/associate" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-handshake fa-3x text-secondary mb-3"></i><h6>Associate Report</h6><p class="small text-muted mb-0">Associate performance and commissions</p></div></div></a></div>
        <div class="col-md-4"><a href="<?= BASE_URL ?>reports/customer" class="text-decoration-none"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-4"><i class="fas fa-user-tie fa-3x text-danger mb-3"></i><h6>Customer Report</h6><p class="small text-muted mb-0">Customer acquisition and demographics</p></div></div></a></div>
    </div>
</div>
