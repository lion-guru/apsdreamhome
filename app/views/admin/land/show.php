<?php $pageTitle = 'Land Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-map-marked-alt me-2"></i>Land Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/land">Land</a></li>
                    <li class="breadcrumb-item active"><?= $land['title'] ?? 'Land' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/land/edit/<?= $land['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="/admin/land" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($land)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-map-marked-alt fa-4x d-block mb-3"></i><h5>Land record not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= $land['title'] ?></h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Area</div><div class="col-sm-8"><strong><?= number_format($land['area_sqft'] ?? 0) ?> sqft</strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Price</div><div class="col-sm-8"><strong class="text-success">₹<?= number_format($land['price'] ?? 0, 2) ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Location</div><div class="col-sm-8"><?= $land['location'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Survey Number</div><div class="col-sm-8"><?= $land['survey_number'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Zoning</div><div class="col-sm-8"><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $land['zoning'] ?? 'N/A' ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Status</div><div class="col-sm-8"><span class="badge bg-<?= ($land['status'] ?? 'available') === 'available' ? 'success' : (($land['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?>-subtle text-<?= ($land['status'] ?? 'available') === 'available' ? 'success' : (($land['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?> rounded-pill px-3"><?= ucfirst($land['status'] ?? 'Available') ?></span></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Description</div><div class="col-sm-8"><?= nl2br($land['description'] ?? 'No description') ?></div></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Owner Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-2"><strong><?= $land['owner_name'] ?? 'N/A' ?></strong></p>
                    <p class="mb-1 text-muted"><i class="fas fa-phone me-1"></i><?= $land['owner_phone'] ?? '-' ?></p>
                    <p class="mb-0 text-muted"><i class="fas fa-calendar me-1"></i>Added <?= date('d M Y', strtotime($land['created_at'] ?? 'now')) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
