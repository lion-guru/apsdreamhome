<?php
$pageTitle = 'Property Details';

// Initialize variables with defaults if not passed from controller
$property = $property ?? [
    'id' => $_GET['id'] ?? 0,
    'title' => 'Property'
];
?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-building me-2"></i>Property Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/properties">Properties</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($property['title'] ?? 'Property') ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/properties/edit/<?= $property['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="/admin/properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($property)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-building fa-4x d-block mb-3"></i>
            <h5>Property not found</h5>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($property['title']) ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Property Type</div>
                            <div class="col-sm-8"><span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= htmlspecialchars($property['type'] ?? 'N/A') ?></span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Price</div>
                            <div class="col-sm-8"><strong class="text-success">₹<?= number_format($property['price'] ?? 0, 2) ?></strong></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Location</div>
                            <div class="col-sm-8"><?= htmlspecialchars($property['address'] ?? '') ?>, <?= htmlspecialchars($property['city'] ?? '') ?> <?= htmlspecialchars($property['state'] ?? '') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Area</div>
                            <div class="col-sm-8"><?= number_format($property['area_sqft'] ?? 0) ?> sqft</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Status</div>
                            <div class="col-sm-8"><span class="badge bg-<?= ($property['status'] ?? 'available') === 'available' ? 'success' : (($property['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?>-subtle text-<?= ($property['status'] ?? 'available') === 'available' ? 'success' : (($property['status'] ?? 'available') === 'sold' ? 'danger' : 'warning') ?> rounded-pill px-3"><?= ucfirst($property['status'] ?? 'Available') ?></span></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-muted">Description</div>
                            <div class="col-sm-8"><?= nl2br(htmlspecialchars($property['description'] ?? 'No description')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Owner Info</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="mb-2"><strong><?= htmlspecialchars($property['owner_name'] ?? 'N/A') ?></strong></p>
                        <p class="mb-1 text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($property['owner_phone'] ?? '-') ?></p>
                        <p class="mb-0 text-muted"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($property['owner_email'] ?? '-') ?></p>
                    </div>
                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Engagement</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="mb-2"><i class="fas fa-eye me-2 text-muted"></i><?= number_format($property['views'] ?? 0) ?> Views</p>
                        <p class="mb-2"><i class="fas fa-heart me-2 text-muted"></i><?= number_format($property['inquiries'] ?? 0) ?> Inquiries</p>
                        <p class="mb-0"><i class="fas fa-calendar me-2 text-muted"></i>Listed <?= date('d M Y', strtotime($property['created_at'] ?? 'now')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>