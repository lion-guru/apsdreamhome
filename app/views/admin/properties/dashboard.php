<?php $pageTitle = 'Properties Dashboard'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-building me-2"></i>Properties Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Properties</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/properties/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Property</a>
                <a href="<?= BASE_URL ?>/admin/properties" class="btn btn-info btn-sm"><i class="fas fa-list me-1"></i>All Properties</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-building fa-2x mb-2 d-block"></i><h6>Total Properties</h6><h3 class="mb-0"><?= number_format($totalProperties ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-check-circle fa-2x mb-2 d-block"></i><h6>Available</h6><h3 class="mb-0"><?= number_format($availableProperties ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-clock fa-2x mb-2 d-block"></i><h6>Pending</h6><h3 class="mb-0"><?= number_format($pendingProperties ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-times-circle fa-2x mb-2 d-block"></i><h6>Inactive</h6><h3 class="mb-0"><?= number_format($inactiveProperties ?? 0) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Properties by Type</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-column fa-4x d-block mb-3"></i>Property type chart area</div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Status breakdown chart</div></div></div>
    </div>
</div>
