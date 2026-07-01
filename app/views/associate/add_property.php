<?php
$page_title = $page_title ?? 'Add Property';
$base = defined('BASE_URL') ? BASE_URL : '';
$states = $states ?? [];
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<style>
    .prop-form .form-label { font-weight: 600; font-size: 0.85rem; color: #374151; margin-bottom: 4px; }
    .prop-form .form-control, .prop-form .form-select { border-radius: 10px; border: 1.5px solid #d1d5db; padding: 10px 14px; font-size: 0.95rem; }
    .prop-form .form-control:focus, .prop-form .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
    .prop-section { background: #fff; border-radius: 14px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .prop-section h6 { font-weight: 700; color: #0d9488; margin-bottom: 14px; font-size: 0.95rem; }
    .type-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .type-option { text-align: center; padding: 12px 8px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: #fff; }
    .type-option input { display: none; }
    .type-option.active { border-color: #6366f1; background: #eef2ff; }
    .type-option i { font-size: 1.5rem; display: block; margin-bottom: 4px; }
    .type-option span { font-size: 0.75rem; font-weight: 600; }
    .photo-upload-zone { border: 2px dashed #c7d2fe; border-radius: 14px; padding: 30px 20px; text-align: center; background: #f5f3ff; cursor: pointer; position: relative; }
    .photo-upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .photo-upload-zone i { font-size: 2rem; color: #6366f1; margin-bottom: 8px; }
    .quick-price { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .quick-price button { padding: 4px 12px; border: 1.5px solid #d1d5db; border-radius: 20px; background: #fff; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
    .quick-price button:hover { border-color: #6366f1; color: #6366f1; }
    @media (max-width: 576px) { .type-selector { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="container-fluid px-3 py-3" style="max-width: 700px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Property</h5>
        <a href="<?= $base ?>/associate/properties" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>My Properties</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show py-2"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <form action="<?= $base ?>/associate/add-property" method="POST" enctype="multipart/form-data" class="prop-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Property Type -->
        <div class="prop-section">
            <h6><i class="fas fa-building"></i>Property Type</h6>
            <div class="type-selector" id="typeSelector">
                <label class="type-option active" data-value="plot"><input type="radio" name="property_type" value="plot" checked><i class="fas fa-map text-success"></i><span>Plot</span></label>
                <label class="type-option" data-value="house"><input type="radio" name="property_type" value="house"><i class="fas fa-home text-primary"></i><span>House</span></label>
                <label class="type-option" data-value="flat"><input type="radio" name="property_type" value="flat"><i class="fas fa-building text-info"></i><span>Flat</span></label>
                <label class="type-option" data-value="shop"><input type="radio" name="property_type" value="shop"><i class="fas fa-store text-warning"></i><span>Shop</span></label>
            </div>
        </div>

        <!-- Listing Type -->
        <div class="prop-section">
            <h6><i class="fas fa-tag"></i>Purpose</h6>
            <div class="d-flex gap-3">
                <div class="form-check flex-fill"><input class="form-check-input" type="radio" name="listing_type" value="sell" id="sell" checked><label class="form-check-label fw-bold" for="sell"><i class="fas fa-indian-rupee-sign text-success me-1"></i>Sell</label></div>
                <div class="form-check flex-fill"><input class="form-check-input" type="radio" name="listing_type" value="rent" id="rent"><label class="form-check-label fw-bold" for="rent"><i class="fas fa-key text-primary me-1"></i>Rent</label></div>
            </div>
        </div>

        <!-- Title & Price -->
        <div class="prop-section">
            <h6><i class="fas fa-info-circle"></i>Details</h6>
            <div class="mb-3">
                <label class="form-label">Property Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. 3BHK House in Civil Lines" required>
            </div>
            <div class="row g-3">
                <div class="col-8">
                    <label class="form-label">Price (₹) *</label>
                    <input type="text" name="price" id="price" class="form-control" placeholder="e.g. 25,00,000" required inputmode="numeric">
                </div>
                <div class="col-4">
                    <label class="form-label">Area (sq.ft.)</label>
                    <input type="text" name="area" class="form-control" placeholder="e.g. 1000" inputmode="numeric">
                </div>
            </div>
            <div class="quick-price">
                <button type="button" onclick="document.getElementById('price').value='10,00,000'">10L</button>
                <button type="button" onclick="document.getElementById('price').value='20,00,000'">20L</button>
                <button type="button" onclick="document.getElementById('price').value='35,00,000'">35L</button>
                <button type="button" onclick="document.getElementById('price').value='50,00,000'">50L</button>
                <button type="button" onclick="document.getElementById('price').value='75,00,000'">75L</button>
                <button type="button" onclick="document.getElementById('price').value='1,00,00,000'">1 Cr</button>
            </div>
        </div>

        <!-- Location -->
        <div class="prop-section">
            <h6><i class="fas fa-map-marker-alt"></i>Location</h6>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">State</label>
                    <select name="state_id" class="form-select">
                        <option value="">Select</option>
                        <?php foreach ($states as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">City/Location *</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Mathura" required>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Full Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Plot no, street, landmark..."></textarea>
            </div>
        </div>

        <!-- Description -->
        <div class="prop-section">
            <h6><i class="fas fa-align-left"></i>Description</h6>
            <textarea name="description" class="form-control" rows="3" placeholder="Plot size, road width, nearby landmarks, features..."></textarea>
        </div>

        <!-- Photo -->
        <div class="prop-section">
            <h6><i class="fas fa-camera"></i>Photo</h6>
            <div class="photo-upload-zone">
                <input type="file" name="property_image" accept="image/*">
                <i class="fas fa-cloud-upload-alt d-block"></i>
                <div class="fw-bold text-primary">Tap to Add Photo</div>
                <div class="text-muted small">Take photo or choose from gallery</div>
            </div>
        </div>

        <!-- Submit -->
        <div class="prop-section text-center">
            <button type="submit" class="btn btn-primary btn-lg w-100 py-3" style="border-radius:14px;font-weight:700;">
                <i class="fas fa-paper-plane me-2"></i>Submit Property
            </button>
            <div class="text-muted small mt-2"><i class="fas fa-shield-alt me-1"></i>Verified before publishing</div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('#typeSelector .type-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('#typeSelector .type-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});
</script>
