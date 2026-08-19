<?php
$p = $property ?? [];
$statusColors = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger','sold'=>'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Edit Resell Property</h1>
        <p class="text-muted mb-0">Update property details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/resell-properties/view/<?= $id ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye me-1"></i>View</a>
        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="<?= BASE_URL ?>/admin/resell-properties/update/<?= $id ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">Property Details</div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Property Title *</label>
                            <input type="text" class="form-control" name="property_title" value="<?= htmlspecialchars($p['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Property Type</label>
                            <select class="form-select" name="property_type">
                                <?php foreach (['plot','house','flat','shop','farmhouse','land','apartment','villa'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($p['property_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-semibold">Location</label>
                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($p['location'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-semibold">Area (sq ft)</label>
                            <input type="number" class="form-control" name="area_sqft" value="<?= (int)($p['area_sqft'] ?? 0) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-semibold">Price (₹)</label>
                            <input type="number" class="form-control" name="price" value="<?= (float)($p['price'] ?? 0) ?>" step="0.01">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">Bedrooms</label>
                            <input type="number" class="form-control" name="bedrooms" value="<?= (int)($p['bedrooms'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">Bathrooms</label>
                            <input type="number" class="form-control" name="bathrooms" value="<?= (int)($p['bathrooms'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">Furnished</label>
                            <select class="form-select" name="furnished">
                                <?php foreach (['unfurnished','semi-furnished','fully-furnished'] as $f): ?>
                                    <option value="<?= $f ?>" <?= ($p['furnished'] ?? 'unfurnished') === $f ? 'selected' : '' ?>><?= ucfirst(str_replace('-',' ',$f)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">Featured</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?= !empty($p['is_featured']) ? 'checked' : '' ?>>
                                <label class="form-check-label">Featured Property</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Address</label>
                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($p['address'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
            </div>
        </form>
    </div>
    <div class="col-md-4">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Status</div>
            <div class="card-body aps-cp-card-body">
                <?php $st = $p['status'] ?? 'pending'; ?>
                <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?> fs-6"><?= ucfirst($st) ?></span>
                <div class="small text-muted mt-2">Created: <?= date('d M Y', strtotime($p['created_at'] ?? 'now')) ?></div>
                <div class="small text-muted">Views: <?= (int)($p['views'] ?? 0) ?></div>
                <?php if (!empty($p['verified_at'])): ?>
                    <div class="small text-muted">Verified: <?= date('d M Y', strtotime($p['verified_at'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Quick Actions</div>
            <div class="card-body aps-cp-card-body d-grid gap-2">
                <a href="<?= BASE_URL ?>/admin/resell-properties/status/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-flag me-1"></i>Update Status</a>
                <a href="<?= BASE_URL ?>/admin/resell-properties/images/<?= $id ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-image me-1"></i>Manage Images</a>
                <a href="<?= BASE_URL ?>/admin/resell-properties/commission/<?= $id ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-percentage me-1"></i>Commission</a>
                <form method="POST" action="<?= BASE_URL ?>/admin/resell-properties/delete/<?= $id ?>" data-aps-confirm="Delete this property? This cannot be undone.">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Delete Property</button>
                </form>
            </div>
        </div>
    </div>
</div>
