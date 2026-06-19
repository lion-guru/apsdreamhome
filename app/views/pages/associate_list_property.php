<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<?php
/**
 * Associate List Property Page
 * Uses associate layout with sidebar
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-home me-2"></i><?= __('assoc_list_heading', [], 'Post Property') ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/associate/properties" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i><?= __('assoc_back_btn', [], 'Back to My Properties') ?>
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i><?= __('assoc_subtitle', [], 'Submit Property Details') ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form action="/associate/list-property/submit" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <!-- Property Purpose -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_purpose', [], 'Purpose of Listing *') ?></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="listing_type" value="sell" id="sell" checked>
                                <label class="form-check-label" for="sell">
                                    <i class="fas fa-tag text-success me-1"></i> <?= __('assoc_listing_sale', [], 'Sell') ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="listing_type" value="rent" id="rent">
                                <label class="form-check-label" for="rent">
                                    <i class="fas fa-key text-primary me-1"></i> <?= __('assoc_listing_rent', [], 'Rent') ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Property Type -->
                    <div class="mb-3">
                        <label for="property_type" class="form-label fw-bold"><?= __('assoc_label_type', [], 'Property Type *') ?></label>
                        <select name="property_type" id="property_type" class="form-select" required>
                            <option value=""><?= __('assoc_type_select', [], 'Select...') ?></option>
                            <option value="plot"><?= __('assoc_type_plot', [], 'Plot (Naksha)') ?></option>
                            <option value="house"><?= __('assoc_type_house', [], 'House / Villa') ?></option>
                            <option value="flat"><?= __('assoc_type_flat', [], 'Flat / Apartment') ?></option>
                            <option value="shop"><?= __('assoc_type_shop', [], 'Shop') ?></option>
                            <option value="farmhouse"><?= __('assoc_type_farmhouse', [], 'Farm House') ?></option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="state_id" class="form-label fw-bold"><?= __('assoc_label_state', [], 'State *') ?></label>
                            <select id="state_id" name="state_id" class="form-select" required>
                                <option value="">Select State...</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= $state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="district_id" class="form-label fw-bold"><?= __('assoc_label_district', [], 'District/City *') ?></label>
                            <select id="district_id" name="location" class="form-select" required disabled>
                                <option value="">Select State First...</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="state_id" id="state_id_hidden">
                    <input type="hidden" name="district_id" id="district_id_hidden">
                    <input type="hidden" name="city_name" id="city_name_hidden">

                    <!-- Price & Area -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label fw-bold"><?= __('assoc_label_price', [], 'Expected Price (&#8377;) *') ?></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="text" name="price" id="price" class="form-control" placeholder="e.g. 25 Lakh" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="area" class="form-label fw-bold"><?= __('assoc_label_area', [], 'Area (sq ft) *') ?></label>
                            <input type="text" name="area" id="area" class="form-control" placeholder="e.g. 1000" required>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-bold"><?= __('assoc_label_your_name', [], 'Your Name *') ?></label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($associate_name); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-bold"><?= __('assoc_label_phone', [], 'Phone Number *') ?></label>
                            <input type="tel" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($associate_phone); ?>" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold"><?= __('assoc_label_email', [], 'Email') ?></label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($associate_email); ?>">
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold"><?= __('assoc_label_desc', [], 'Full Address / Location Details') ?></label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Plot size, road width, nearby landmarks..."></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="property_image" class="form-label fw-bold"><?= __('assoc_label_photos', [], 'Property Image') ?></label>
                        <input type="file" name="property_image" id="property_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Upload JPG, PNG or WEBP (Max 5MB)</small>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-paper-plane me-2"></i><?= __('assoc_submit_btn', [], 'Submit Property Listing') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Benefits -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-star me-2"></i><?= __('assoc_benefits_heading', [], 'Associate Benefits') ?></h6>
            </div>
            <div class="card-body aps-cp-card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('assoc_benefit_commission', [], '2% Commission on Sale') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('assoc_benefit_buyer', [], 'Direct Buyer Contact') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('assoc_benefit_verify', [], 'Property Verification') ?></li>
                    <li><i class="fas fa-check text-success me-2"></i><?= __('assoc_benefit_track', [], 'Track in Dashboard') ?></li>
                </ul>
            </div>
        </div>

        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i><?= __('assoc_help_heading', [], 'Need Help?') ?></h6>
            <p class="small mb-0"><?= __('assoc_help_contact', [], 'Contact support at') ?><br>
            <strong><?= htmlspecialchars($phoneDisplay) ?></strong></p>
        </div>
    </div>
</div>

<script>
// Location cascade
document.getElementById('state_id').addEventListener('change', async function() {
    const stateId = this.value;
    const districtSelect = document.getElementById('district_id');

    if (!stateId) {
        districtSelect.innerHTML = '<option value="">Select State First...</option>';
        districtSelect.disabled = true;
        return;
    }

    districtSelect.disabled = true;
    districtSelect.innerHTML = '<option value="">Loading...</option>';

    try {
        const response = await fetch('/api/locations/districts?state_id=' + stateId);
        const districts = await response.json();

        districtSelect.innerHTML = '<option value="">Select District...</option>';
        districts.forEach(d => {
            const option = document.createElement('option');
            option.value = d.name;
            option.dataset.id = d.id;
            option.textContent = d.name;
            districtSelect.appendChild(option);
        });
        districtSelect.disabled = false;
    } catch (error) {
        districtSelect.innerHTML = '<option value="">Error loading</option>';
    }
});

document.getElementById('district_id').addEventListener('change', function() {
    const stateSelect = document.getElementById('state_id');
    const selectedOption = this.options[this.selectedIndex];

    document.getElementById('state_id_hidden').value = stateSelect.value;
    document.getElementById('district_id_hidden').value = selectedOption.dataset.id || '';
    document.getElementById('city_name_hidden').value = this.value;
});
</script>
