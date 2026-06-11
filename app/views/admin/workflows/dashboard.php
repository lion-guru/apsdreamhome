<?php $pageTitle = 'Workflow Dashboard'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-diagram-project me-2"></i>Workflow Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Workflows</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/workflows/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create Workflow</a>
                <a href="/admin/workflows/list" class="btn btn-info btn-sm"><i class="fas fa-list me-1"></i>All Workflows</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Workflows</h6><h3 class="mb-0"><?= number_format($totalWorkflows ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Active</h6><h3 class="mb-0"><?= number_format($activeWorkflows ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0"><?= number_format($pendingWorkflows ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Completed</h6><h3 class="mb-0"><?= number_format($completedWorkflows ?? 0) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Workflow Status</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Status distribution chart</div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-clock-rotate-left fa-4x d-block mb-3"></i>Recent workflow activity</div></div></div>
    </div>
</div>
