<?php $pageTitle = 'Site Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-map-pin me-2"></i>Site Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/sites">Sites</a></li>
                    <li class="breadcrumb-item active"><?= $site['name'] ?? 'Site' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/sites/<?= $site['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/sites" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($site)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-map-pin fa-4x d-block mb-3"></i><h5>Site not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= $site['name'] ?></h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Location</div><div class="col-sm-8"><strong><?= $site['location'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Total Area</div><div class="col-sm-8"><?= number_format($site['total_area'] ?? 0) ?> sqft</div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Developed Area</div><div class="col-sm-8"><?= number_format($site['developed_area'] ?? 0) ?> sqft</div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Total Plots</div><div class="col-sm-8"><?= number_format($site['total_plots'] ?? 0) ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Available Plots</div><div class="col-sm-8"><span class="badge bg-success-subtle text-success rounded-pill px-3"><?= number_format($site['available_plots'] ?? 0) ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Status</div><div class="col-sm-8"><span class="badge bg-<?= ($site['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($site['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($site['status'] ?? 'Active') ?></span></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Description</div><div class="col-sm-8"><?= nl2br($site['description'] ?? 'No description') ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Site Stats</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><small class="text-muted d-block">Development Progress</small><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?= ($site['total_area'] ?? 0) > 0 ? round(($site['developed_area'] ?? 0) / $site['total_area'] * 100) : 0 ?>%"></div></div><small class="text-muted"><?= ($site['total_area'] ?? 0) > 0 ? round(($site['developed_area'] ?? 0) / $site['total_area'] * 100) : 0 ?>% developed</small></div>
                    <div class="mb-3"><small class="text-muted d-block">Plot Availability</small><strong class="text-success"><?= number_format($site['available_plots'] ?? 0) ?></strong> / <?= number_format($site['total_plots'] ?? 0) ?> available</div>
                    <div class="mb-3"><small class="text-muted d-block">Created</small><?= date('d M Y', strtotime($site['created_at'] ?? 'now')) ?></div>
                    <div><small class="text-muted d-block">Last Updated</small><?= date('d M Y', strtotime($site['updated_at'] ?? 'now')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
