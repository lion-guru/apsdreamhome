<?php
$p = $property ?? [];
$statusColors = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger','sold'=>'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars($p['name'] ?? 'Property Details') ?></h1>
        <p class="text-muted mb-0">Property listing details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/resell-properties/edit/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Property Information</div>
            <div class="card-body aps-cp-card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Property Type</div>
                        <div class="fw-semibold"><?= ucfirst(htmlspecialchars($p['property_type'] ?? 'N/A')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Status</div>
                        <?php $st = $p['status'] ?? 'pending'; ?>
                        <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>"><?= ucfirst($st) ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Price</div>
                        <div class="fw-bold text-primary fs-5">₹<?= number_format((float)($p['price'] ?? 0)) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Area</div>
                        <div class="fw-semibold"><?= number_format((int)($p['area_sqft'] ?? 0)) ?> sq ft</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Furnished</div>
                        <div class="fw-semibold"><?= ucfirst(str_replace('-',' ', $p['furnished'] ?? 'unfurnished')) ?></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Bedrooms</div>
                        <div class="fw-semibold"><?= (int)($p['bedrooms'] ?? 0) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Bathrooms</div>
                        <div class="fw-semibold"><?= (int)($p['bathrooms'] ?? 0) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Views</div>
                        <div class="fw-semibold"><?= (int)($p['views'] ?? 0) ?></div>
                    </div>
                </div>
                <?php if (!empty($p['address'])): ?>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Address</div>
                    <div><?= nl2br(htmlspecialchars($p['address'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['location'])): ?>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Location</div>
                    <div><?= htmlspecialchars($p['location']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['description'])): ?>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Description</div>
                    <div><?= nl2br(htmlspecialchars($p['description'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Seller Info</div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-2"><strong><?= htmlspecialchars($p['seller_name'] ?? 'N/A') ?></strong></div>
                <?php if (!empty($p['seller_phone'])): ?>
                    <div class="small"><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($p['seller_phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($p['seller_email'])): ?>
                    <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($p['seller_email']) ?></div>
                <?php endif; ?>
                <div class="small text-muted mt-2">Posted by: <?= htmlspecialchars($p['seller_name'] ?? 'Admin') ?></div>
            </div>
        </div>
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Timeline</div>
            <div class="card-body aps-cp-card-body">
                <div class="small mb-2"><i class="fas fa-clock me-1 text-muted"></i>Created: <?= date('d M Y, H:i', strtotime($p['created_at'] ?? 'now')) ?></div>
                <div class="small mb-2"><i class="fas fa-sync me-1 text-muted"></i>Updated: <?= date('d M Y, H:i', strtotime($p['updated_at'] ?? 'now')) ?></div>
                <?php if (!empty($p['verified_at'])): ?>
                    <div class="small mb-2"><i class="fas fa-check-circle me-1 text-success"></i>Verified: <?= date('d M Y, H:i', strtotime($p['verified_at'])) ?></div>
                <?php endif; ?>
                <?php if (!empty($p['sold_at'])): ?>
                    <div class="small"><i class="fas fa-handshake me-1 text-info"></i>Sold: <?= date('d M Y, H:i', strtotime($p['sold_at'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Quick Actions</div>
            <div class="card-body aps-cp-card-body d-grid gap-2">
                <a href="<?= BASE_URL ?>/admin/resell-properties/edit/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Edit Property</a>
                <a href="<?= BASE_URL ?>/admin/resell-properties/status/<?= $id ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-flag me-1"></i>Update Status</a>
                <a href="<?= BASE_URL ?>/admin/resell-properties/images/<?= $id ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-image me-1"></i>Manage Images</a>
            </div>
        </div>
    </div>
</div>
