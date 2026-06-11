<?php $pageTitle = 'Plot Development Tracking'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-hard-hat me-2"></i>Plot Development Tracking</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/plots">Plots</a></li>
                    <li class="breadcrumb-item active">Development</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Under Development</h6><h3 class="mb-0"><?= number_format($underDevelopment ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Construction Phase</h6><h3 class="mb-0"><?= number_format($inConstruction ?? 0) ?></h3></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Completed</h6><h3 class="mb-0"><?= number_format($completed ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Development Projects</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Project</th><th>Location</th><th>Total Plots</th><th>Developed</th><th>Progress</th><th class="text-end pe-4">Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-hard-hat fa-3x d-block mb-3"></i>No development projects tracked</td></tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                            <tr><td class="ps-4"><strong><?= $p['name'] ?></strong></td><td><?= $p['location'] ?? '-' ?></td><td><?= $p['total_plots'] ?? 0 ?></td><td><?= $p['developed_plots'] ?? 0 ?></td><td style="min-width:150px"><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?= ($p['total_plots'] ?? 0) > 0 ? round(($p['developed_plots'] ?? 0) / $p['total_plots'] * 100) : 0 ?>%"></div></div><small class="text-muted"><?= ($p['total_plots'] ?? 0) > 0 ? round(($p['developed_plots'] ?? 0) / $p['total_plots'] * 100) : 0 ?>%</small></td><td class="text-end pe-4"><span class="badge bg-<?= ($p['status'] ?? 'active') === 'completed' ? 'success' : (($p['status'] ?? 'active') === 'active' ? 'primary' : 'secondary') ?>-subtle text-<?= ($p['status'] ?? 'active') === 'completed' ? 'success' : (($p['status'] ?? 'active') === 'active' ? 'primary' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($p['status'] ?? 'Active') ?></span></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
