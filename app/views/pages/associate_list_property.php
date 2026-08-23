<?php
$page_title = $page_title ?? 'Post Property';
$base = defined('BASE_URL') ? BASE_URL : '';
$states = $states ?? [];
$success = $success ?? null;
$error = $error ?? null;
$associate_name = $associate_name ?? '';
$associate_phone = $associate_phone ?? '';
$associate_email = $associate_email ?? '';
?>

<style>
    .prop-form .form-label { font-weight: 600; font-size: 0.85rem; color: #374151; margin-bottom: 4px; }
    .prop-form .form-control, .prop-form .form-select { border-radius: 10px; border: 1.5px solid #d1d5db; padding: 10px 14px; font-size: 0.95rem; }
    .prop-form .form-control:focus, .prop-form .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
    .prop-section { background: #fff; border-radius: 14px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .prop-section h6 { font-weight: 700; color: #0d9488; margin-bottom: 14px; font-size: 0.95rem; }
    .prop-section h6 i { margin-right: 6px; }
    .photo-upload-zone { border: 2px dashed #c7d2fe; border-radius: 14px; padding: 30px 20px; text-align: center; background: #f5f3ff; cursor: pointer; transition: all 0.2s; position: relative; }
    .photo-upload-zone:hover, .photo-upload-zone.dragover { border-color: #6366f1; background: #ede9fe; }
    .photo-upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .photo-upload-zone i { font-size: 2rem; color: #6366f1; margin-bottom: 8px; }
    .photo-preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px; }
    .photo-preview-item { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 1; background: #f3f4f6; }
    .photo-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .photo-preview-item .remove-btn { position: absolute; top: 4px; right: 4px; background: #ef4444; color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .type-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .type-option { text-align: center; padding: 12px 8px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: #fff; }
    .type-option input { display: none; }
    .type-option.active { border-color: #6366f1; background: #eef2ff; }
    .type-option i { font-size: 1.5rem; display: block; margin-bottom: 4px; }
    .type-option span { font-size: 0.75rem; font-weight: 600; }
    .price-input-group { position: relative; }
    .price-input-group .form-control { padding-left: 30px; }
    .price-input-group::before { content: '₹'; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600; z-index: 1; }
    .quick-price { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .quick-price button { padding: 4px 12px; border: 1.5px solid #d1d5db; border-radius: 20px; background: #fff; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .quick-price button:hover { border-color: #6366f1; color: #6366f1; }
    @media (max-width: 576px) {
        .type-selector { grid-template-columns: repeat(2, 1fr); }
        .photo-preview-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="container-fluid px-3 py-3 style-63221">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold"><i class="fas fa-home me-2 text-primary"></i>Post Property</h5>
        <a href="<?= $base ?>/associate/properties" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>My Properties</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= $base ?>/associate/list-property/submit" method="POST" enctype="multipart/form-data" class="prop-form" id="propForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Section 1: Property Type -->
        <div class="prop-section">
            <h6><i class="fas fa-building"></i>Property Type</h6>
            <div class="type-selector" id="propertyTypeSelector">
                <label class="type-option active" data-value="plot">
                    <input type="radio" name="property_type" value="plot" checked>
                    <i class="fas fa-map text-success"></i>
                    <span>Plot / Naksha</span>
                </label>
                <label class="type-option" data-value="house">
                    <input type="radio" name="property_type" value="house">
                    <i class="fas fa-home text-primary"></i>
                    <span>House / Villa</span>
                </label>
                <label class="type-option" data-value="flat">
                    <input type="radio" name="property_type" value="flat">
                    <i class="fas fa-building text-info"></i>
                    <span>Flat / Apartment</span>
                </label>
                <label class="type-option" data-value="shop">
                    <input type="radio" name="property_type" value="shop">
                    <i class="fas fa-store text-warning"></i>
                    <span>Shop / Office</span>
                </label>
            </div>
        </div>

        <!-- Section 2: Listing Purpose -->
        <div class="prop-section">
            <h6><i class="fas fa-tag"></i>Purpose</h6>
            <div class="d-flex gap-3">
                <div class="form-check flex-fill">
                    <input class="form-check-input" type="radio" name="listing_type" value="sell" id="purposeSell" checked>
                    <label class="form-check-label fw-bold" for="purposeSell">
                        <i class="fas fa-indian-rupee-sign text-success me-1"></i> Sell
                    </label>
                </div>
                <div class="form-check flex-fill">
                    <input class="form-check-input" type="radio" name="listing_type" value="rent" id="purposeRent">
                    <label class="form-check-label fw-bold" for="purposeRent">
                        <i class="fas fa-key text-primary me-1"></i> Rent
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 3: Price & Area -->
        <div class="prop-section">
            <h6><i class="fas fa-indian-rupee-sign"></i>Price & Area</h6>
            <div class="row g-3">
                <div class="col-8">
                    <label class="form-label">Expected Price *</label>
                    <input type="text" name="price" id="price" class="form-control" placeholder="e.g. 25,00,000" required inputmode="numeric">
                </div>
                <div class="col-4">
                    <label class="form-label">Unit</label>
                    <select name="price_unit" class="form-select">
                        <option value="total">Total</option>
                        <option value="lakh">Lakh</option>
                        <option value="cr">Crore</option>
                        <option value="sqft">Per Sq Ft</option>
                    </select>
                </div>
            </div>
            <div class="quick-price" id="quickPrices">
                <button type="button" onclick="setPrice('10,00,000')">10L</button>
                <button type="button" onclick="setPrice('20,00,000')">20L</button>
                <button type="button" onclick="setPrice('35,00,000')">35L</button>
                <button type="button" onclick="setPrice('50,00,000')">50L</button>
                <button type="button" onclick="setPrice('75,00,000')">75L</button>
                <button type="button" onclick="setPrice('1,00,00,000')">1 Cr</button>
                <button type="button" onclick="setPrice('1,50,00,000')">1.5 Cr</button>
                <button type="button" onclick="setPrice('2,00,00,000')">2 Cr</button>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-6">
                    <label class="form-label">Area *</label>
                    <input type="text" name="area" id="area" class="form-control" placeholder="e.g. 1000" required inputmode="numeric">
                </div>
                <div class="col-6">
                    <label class="form-label">Area Unit</label>
                    <select name="area_unit" class="form-select">
                        <option value="sqft">Sq Ft</option>
                        <option value="sqm">Sq Meter</option>
                        <option value="acre">Acre</option>
                        <option value="bigha">Bigha</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 4: Property Details -->
        <div class="prop-section" id="detailsSection">
            <h6><i class="fas fa-info-circle"></i>Property Details</h6>
            <div class="row g-3">
                <div class="col-4" id="bedroomField">
                    <label class="form-label">Bedrooms</label>
                    <select name="bedrooms" class="form-select">
                        <option value="">-</option>
                        <option value="1">1 BHK</option>
                        <option value="2">2 BHK</option>
                        <option value="3">3 BHK</option>
                        <option value="4">4 BHK</option>
                        <option value="5">5+ BHK</option>
                    </select>
                </div>
                <div class="col-4" id="bathroomField">
                    <label class="form-label">Bathrooms</label>
                    <select name="bathrooms" class="form-select">
                        <option value="">-</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4+</option>
                    </select>
                </div>
                <div class="col-4" id="furnishingField">
                    <label class="form-label">Furnishing</label>
                    <select name="furnishing" class="form-select">
                        <option value="">-</option>
                        <option value="unfurnished">Unfurnished</option>
                        <option value="semi">Semi-Furnished</option>
                        <option value="fully">Fully-Furnished</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-6">
                    <label class="form-label">Facing</label>
                    <select name="facing" class="form-select">
                        <option value="">Select</option>
                        <option value="north">North</option>
                        <option value="south">South</option>
                        <option value="east">East</option>
                        <option value="west">West</option>
                        <option value="north-east">North-East</option>
                        <option value="north-west">North-West</option>
                        <option value="south-east">South-East</option>
                        <option value="south-west">South-West</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Floor</label>
                    <input type="text" name="floor" class="form-control" placeholder="e.g. 2nd Floor" inputmode="numeric">
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-6">
                    <label class="form-label">Ownership Type</label>
                    <select name="ownership_type" class="form-select">
                        <option value="freehold">Freehold</option>
                        <option value="leasehold">Leasehold</option>
                        <option value="coop">Cooperative</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Possession</label>
                    <select name="possession" class="form-select">
                        <option value="ready">Ready to Move</option>
                        <option value="under">Under Construction</option>
                        <option value="soon">Coming Soon</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 5: Location -->
        <div class="prop-section">
            <h6><i class="fas fa-map-marker-alt"></i>Location</h6>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">State *</label>
                    <select id="state_id" name="state_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?= $state['id'] ?>"><?= htmlspecialchars($state['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">District *</label>
                    <select id="district_id" name="location" class="form-select" required disabled>
                        <option value="">Select State</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Full Address / Landmark *</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Plot no, street, nearby landmark..." required></textarea>
            </div>
        </div>

        <!-- Section 6: Photos -->
        <div class="prop-section">
            <h6><i class="fas fa-camera"></i>Property Photos</h6>
            <div class="photo-upload-zone" id="photoDropZone">
                <input type="file" name="property_image" id="photoInput" accept="image/*">
                <i class="fas fa-cloud-upload-alt d-block"></i>
                <div class="fw-bold text-primary">Tap to Add Photo</div>
                <div class="text-muted small">Take photo or choose from gallery</div>
                <div class="text-muted small mt-1">JPG, PNG, WEBP (max 5MB)</div>
            </div>
            <div class="photo-preview-grid" id="photoPreviewGrid"></div>
        </div>

        <!-- Section 7: Contact -->
        <div class="prop-section">
            <h6><i class="fas fa-phone"></i>Contact Details</h6>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Your Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($associate_name ?? '') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Phone *</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($associate_phone ?? '') ?>" required inputmode="tel">
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($associate_email ?? '') ?>">
            </div>
        </div>

        <!-- Submit -->
        <div class="prop-section text-center">
            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 style-80393">
                <i class="fas fa-paper-plane me-2"></i>Post Property
            </button>
            <div class="text-muted small mt-2">
                <i class="fas fa-shield-alt me-1"></i>Property will be verified before publishing
            </div>
        </div>
    </form>
</div>

<script>
// Type selector
document.querySelectorAll('#propertyTypeSelector .type-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('#propertyTypeSelector .type-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
        updateFieldVisibility(this.dataset.value);
    });
});

function updateFieldVisibility(type) {
    const showDetails = ['house', 'flat', 'shop'].includes(type);
    document.getElementById('detailsSection').style.display = showDetails ? '' : 'none';
}

// Quick price buttons
function setPrice(val) {
    document.getElementById('price').value = val;
}

// Photo upload with preview
const photoInput = document.getElementById('photoInput');
const photoGrid = document.getElementById('photoPreviewGrid');
const dropZone = document.getElementById('photoDropZone');
photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    photoGrid.innerHTML = '';
    const reader = new FileReader();
    reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'photo-preview-item';
        div.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button type="button" class="remove-btn" onclick="this.parentElement.remove(); photoInput.value=\'\'"><i class="fas fa-times"></i></button>';
        photoGrid.appendChild(div);
    };
    reader.readAsDataURL(file);
});

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});
dropZone.addEventListener('dragleave', function() {
    this.classList.remove('dragover');
});
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        photoInput.files = e.dataTransfer.files;
        photoInput.dispatchEvent(new Event('change'));
    }
});

// Location cascade
document.getElementById('state_id').addEventListener('change', async function() {
    const stateId = this.value;
    const districtSelect = document.getElementById('district_id');
    if (!stateId) {
        districtSelect.innerHTML = '<option value="">Select State</option>';
        districtSelect.disabled = true;
        return;
    }
    districtSelect.disabled = true;
    districtSelect.innerHTML = '<option value="">Loading...</option>';
    try {
        const resp = await fetch('<?= BASE_URL ?>/api/locations/districts?state_id=' + stateId);
        const districts = await resp.json();
        districtSelect.innerHTML = '<option value="">Select District</option>';
        districts.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.name;
            opt.textContent = d.name;
            districtSelect.appendChild(opt);
        });
        districtSelect.disabled = false;
    } catch (e) {
        districtSelect.innerHTML = '<option value="">Error loading</option>';
    }
});

// Init
updateFieldVisibility('plot');
</script>
