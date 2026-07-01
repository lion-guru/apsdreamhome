<?php
$page_title = 'Edit Property - APS Dream Home';
$property = $property ?? [];
$states = $states ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-edit text-primary me-2"></i>Edit Property</h4>
            <small class="text-muted">Update your property listing details</small>
        </div>
        <a href="<?= BASE_URL ?>/associate/properties" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Properties
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/associate/properties/update/<?= (int)$property['id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Property Title *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($property['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Property Type *</label>
                        <select name="property_type" class="form-select" required>
                            <?php foreach (['residential_plot' => 'Residential Plot', 'commercial_plot' => 'Commercial Plot', 'apartment' => 'Apartment', 'villa' => 'Villa', 'house' => 'House', 'other' => 'Other'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($property['property_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Listing Type *</label>
                        <select name="listing_type" class="form-select" required>
                            <option value="sell" <?= ($property['listing_type'] ?? '') === 'sell' ? 'selected' : '' ?>>Sell</option>
                            <option value="rent" <?= ($property['listing_type'] ?? '') === 'rent' ? 'selected' : '' ?>>Rent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Price (₹) *</label>
                        <input type="number" name="price" class="form-control" value="<?= (int)($property['price'] ?? 0) ?>" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Area (sq ft)</label>
                        <input type="number" name="area" class="form-control" value="<?= (int)($property['area_sqft'] ?? 0) ?>" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Location / Address</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($property['address'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Replace Image (optional)</label>
                        <input type="file" name="property_image" class="form-control" accept="image/*">
                        <?php if (!empty($property['image'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current:</small><br>
                                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($property['image']) ?>" alt="Current" style="max-height:80px;border-radius:8px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="w-100">
                            <span class="badge bg-<?= ($property['status'] ?? '') === 'approved' ? 'success' : (($property['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?> mb-2">
                                Status: <?= ucfirst($property['status'] ?? 'pending') ?>
                            </span>
                            <?php if (($property['status'] ?? '') === 'pending'): ?>
                                <small class="text-muted d-block">Your property is under review. Changes will be re-verified.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-danger" onclick="if(confirm('Archive this property? It will be hidden from listings.')) document.getElementById('deleteForm').submit();">
                        <i class="fas fa-archive me-1"></i>Archive Property
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
            <form id="deleteForm" action="<?= BASE_URL ?>/associate/properties/delete/<?= (int)$property['id'] ?>" method="POST" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
        </div>
    </div>
</div>
