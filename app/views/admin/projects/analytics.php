<?php $pageTitle = 'Project Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-bar me-2"></i>Project Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/projects">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/projects/show/<?= $project['id'] ?? 0 ?>"><?= $project['name'] ?? 'Project' ?></a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($project)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-chart-bar fa-4x d-block mb-3"></i><h5>Project not found</h5></div>
    <?php else: ?>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Units</h6><h3 class="mb-0"><?= number_format($project['total_units'] ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Sold</h6><h3 class="mb-0"><?= number_format(($project['total_units'] ?? 0) - ($project['available_units'] ?? 0)) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Available</h6><h3 class="mb-0"><?= number_format($project['available_units'] ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Booking Value</h6><h3 class="mb-0">₹<?= number_format($bookingValue ?? 0, 2) ?></h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Bookings</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-line fa-4x d-block mb-3"></i>Monthly booking trend chart</div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Unit Type Distribution</h5></div><div class="card-body text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Unit type breakdown chart</div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Date</th><th>Event</th><th>Customer</th><th class="text-end pe-4">Amount</th></tr></thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-clock fa-2x d-block mb-2"></i>No recent activity</td></tr>
                        <?php else: ?>
                            <?php foreach ($activities as $a): ?>
                            <tr><td class="ps-4"><?= date('d M Y', strtotime($a['date'] ?? 'now')) ?></td><td><?= $a['event'] ?? '-' ?></td><td><?= $a['customer_name'] ?? '-' ?></td><td class="text-end pe-4 fw-bold text-success">+₹<?= number_format($a['amount'] ?? 0, 2) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
