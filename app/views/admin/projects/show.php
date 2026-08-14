<?php $pageTitle = 'Project Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-folder-open me-2"></i>Project Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/projects">Projects</a></li>
                    <li class="breadcrumb-item active"><?= $project['name'] ?? 'Project' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $project['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/projects/analytics/<?= $project['id'] ?? 0 ?>" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i>Analytics</a>
                <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($project)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-folder-open fa-4x d-block mb-3"></i><h5>Project not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= $project['name'] ?></h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Location</div><div class="col-sm-8"><strong><?= $project['location'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Type</div><div class="col-sm-8"><span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= $project['type'] ?? 'Residential' ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Status</div><div class="col-sm-8"><span class="badge bg-<?= ($project['status'] ?? 'active') === 'completed' ? 'success' : (($project['status'] ?? 'active') === 'active' ? 'primary' : 'secondary') ?>-subtle text-<?= ($project['status'] ?? 'active') === 'completed' ? 'success' : (($project['status'] ?? 'active') === 'active' ? 'primary' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($project['status'] ?? 'Active') ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Total Units</div><div class="col-sm-8"><?= number_format($project['total_units'] ?? 0) ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Available Units</div><div class="col-sm-8"><span class="badge bg-success-subtle text-success rounded-pill px-3"><?= number_format($project['available_units'] ?? 0) ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Launch Date</div><div class="col-sm-8"><?= $project['launch_date'] ? date('d M Y', strtotime($project['launch_date'])) : '-' ?></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Description</div><div class="col-sm-8"><?= nl2br($project['description'] ?? 'No description') ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Project Stats</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><small class="text-muted d-block">Sales Progress</small><div class="progress" class="style-32124"><div class="progress-bar bg-success" class="style-30823"></div></div><small class="text-muted"><?= ($project['total_units'] ?? 0) > 0 ? round((($project['total_units'] ?? 0) - ($project['available_units'] ?? 0)) / $project['total_units'] * 100) : 0 ?>% sold</small></div>
                    <div class="mb-3"><small class="text-muted d-block">Plots/Units</small><strong class="text-primary"><?= number_format($project['total_units'] ?? 0) ?></strong> total</div>
                    <div class="mb-3"><small class="text-muted d-block">Available</small><strong class="text-success"><?= number_format($project['available_units'] ?? 0) ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Created</small><?= date('d M Y', strtotime($project['created_at'] ?? 'now')) ?></div>
                    <div><small class="text-muted d-block">Last Updated</small><?= date('d M Y', strtotime($project['updated_at'] ?? 'now')) ?></div>
                </div>
            </div>
            <?php if ($project['image_url']): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0"><img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded" alt="<?= $project['name'] ?>" class="style-90537"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
