<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '+91 92771 21112'); ?>
<?php
/**
 * List Property Page - 3-Step Wizard with Modern UI
 * Step 1: Type & Listing | Step 2: Location & Details | Step 3: Photos & Contact
 */
if (!function_exists('__')) {
    require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
}

$page_title = $page_title ?? 'List Your Property - Free Property Posting';

$success = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$error = $_SESSION['error'] ?? $_SESSION['flash_error'] ?? null;
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['flash_success'], $_SESSION['flash_error']);

$isCustomer = !empty($_SESSION['user_id']);
$isAssociate = !empty($_SESSION['associate_id']);
$isAgent = !empty($_SESSION['agent_id']);
$isLoggedIn = $isCustomer || $isAssociate || $isAgent;

$userName = $isCustomer ? ($_SESSION['user_name'] ?? '') : ($isAssociate ? ($_SESSION['associate_name'] ?? '') : ($isAgent ? ($_SESSION['agent_name'] ?? '') : ''));
$userPhone = $isCustomer ? ($_SESSION['user_phone'] ?? '') : ($isAssociate ? ($_SESSION['associate_phone'] ?? '') : ($isAgent ? ($_SESSION['agent_phone'] ?? '') : ''));
$userEmail = $isCustomer ? ($_SESSION['user_email'] ?? '') : ($isAssociate ? ($_SESSION['associate_email'] ?? '') : ($isAgent ? ($_SESSION['agent_email'] ?? '') : ''));

$db = \App\Core\Database\Database::getInstance();
try {
    $states = $db->fetchAll("SELECT id, name FROM states WHERE is_active = 1 ORDER BY name LIMIT 50");
} catch (\Throwable $e) {
    $states = [];
}
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-paper-plane me-2"></i><?= __('list_property_hero_title') ?></h2>
            <p><?= __('list_property_hero_lead') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <div class="aps-cp-hero-actions justify-content-md-end">
                <a href="tel:<?= $phoneRaw ?>" class="btn btn-light">
                    <i class="fas fa-phone me-2"></i><?= __('list_property_call_label') ?>: <?= $phoneDisplay ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <form action="<?= BASE_URL ?>/list-property/submit" method="POST" enctype="multipart/form-data" id="listPropertyForm" data-aps-ajax data-aps-success-redirect="<?= BASE_URL ?>/list-property" data-aps-redirect-delay="1800">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="selected_state_id" id="selected_state_id" value="">
            <input type="hidden" name="selected_district_id" id="selected_district_id" value="">
            <input type="hidden" name="selected_city_name" id="selected_city_name" value="">

            <div class="aps-cp-wizard" data-aps-wizard>
                <div class="aps-cp-wizard-header">
                    <h3 class="aps-cp-wizard-title"><i class="fas fa-paper-plane me-2"></i><?= __('list_property_card_title') ?></h3>
                    <p class="aps-cp-wizard-subtitle"><?= __('list_property_wizard_subtitle', null, 'Complete 3 simple steps to list your property for free.') ?></p>
                </div>

                <ol class="aps-cp-wizard-steps" role="list">
                    <li class="aps-cp-wizard-step">
                        <span class="aps-cp-wizard-step-num"><span>1</span></span>
                        <span class="aps-cp-wizard-step-label"><?= __('list_property_step1_label', null, 'Type & Listing') ?></span>
                    </li>
                    <li class="aps-cp-wizard-step">
                        <span class="aps-cp-wizard-step-num"><span>2</span></span>
                        <span class="aps-cp-wizard-step-label"><?= __('list_property_step2_label', null, 'Location & Details') ?></span>
                    </li>
                    <li class="aps-cp-wizard-step">
                        <span class="aps-cp-wizard-step-num"><span>3</span></span>
                        <span class="aps-cp-wizard-step-label"><?= __('list_property_step3_label', null, 'Photos & Contact') ?></span>
                    </li>
                </ol>

                <div class="aps-cp-wizard-body">
                    <div class="aps-cp-wizard-panel active" data-panel="0">
                        <h4 class="mb-3"><i class="fas fa-tag me-2 text-primary"></i><?= __('list_property_purpose_question', null, 'What do you want to do?') ?></h4>
                        <div class="mb-4">
                            <div class="aps-cp-pill-group" role="tablist" aria-label="<?= __('list_property_purpose_question') ?>">
                                <button type="button" class="aps-cp-pill is-active" data-pill="sell" aria-pressed="true">
                                    <i class="fas fa-tag"></i> <?= __('sell') ?>
                                </button>
                                <button type="button" class="aps-cp-pill" data-pill="rent" aria-pressed="false">
                                    <i class="fas fa-key"></i> <?= __('rent') ?>
                                </button>
                            </div>
                            <input type="hidden" name="listing_type" id="listing_type" value="sell">
                        </div>

                        <h4 class="mb-3"><i class="fas fa-building me-2 text-primary"></i><?= __('list_property_label_property_type') ?> *</h4>
                        <div class="aps-cp-type-picker" role="radiogroup" aria-label="<?= __('list_property_label_property_type') ?>">
                            <label class="aps-cp-type-option">
                                <input type="radio" name="property_type" value="plot" required>
                                <i class="fas fa-map-marked-alt aps-cp-type-option-icon"></i>
                                <div class="aps-cp-type-option-label"><?= __('list_property_type_plot') ?></div>
                                <div class="aps-cp-type-option-desc"><?= __('list_property_type_plot_desc', null, 'Open land / plot') ?></div>
                            </label>
                            <label class="aps-cp-type-option">
                                <input type="radio" name="property_type" value="house">
                                <i class="fas fa-home aps-cp-type-option-icon"></i>
                                <div class="aps-cp-type-option-label"><?= __('list_property_type_house') ?></div>
                                <div class="aps-cp-type-option-desc"><?= __('list_property_type_house_desc', null, 'Independent house') ?></div>
                            </label>
                            <label class="aps-cp-type-option">
                                <input type="radio" name="property_type" value="flat">
                                <i class="fas fa-building aps-cp-type-option-icon"></i>
                                <div class="aps-cp-type-option-label"><?= __('list_property_type_flat') ?></div>
                                <div class="aps-cp-type-option-desc"><?= __('list_property_type_flat_desc', null, 'Apartment / flat') ?></div>
                            </label>
                            <label class="aps-cp-type-option">
                                <input type="radio" name="property_type" value="shop">
                                <i class="fas fa-store aps-cp-type-option-icon"></i>
                                <div class="aps-cp-type-option-label"><?= __('list_property_type_shop') ?></div>
                                <div class="aps-cp-type-option-desc"><?= __('list_property_type_shop_desc', null, 'Commercial shop') ?></div>
                            </label>
                            <label class="aps-cp-type-option">
                                <input type="radio" name="property_type" value="farmhouse">
                                <i class="fas fa-tractor aps-cp-type-option-icon"></i>
                                <div class="aps-cp-type-option-label"><?= __('list_property_type_farmhouse') ?></div>
                                <div class="aps-cp-type-option-desc"><?= __('list_property_type_farmhouse_desc', null, 'Farm / farmhouse') ?></div>
                            </label>
                        </div>
                    </div>

                    <div class="aps-cp-wizard-panel" data-panel="1">
                        <h4 class="mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= __('list_property_location_heading', null, 'Where is your property?') ?></h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="state_id" class="form-label fw-bold"><?= __('list_property_label_state') ?> *</label>
                                <select id="state_id" name="state_id" class="form-select" required aria-label="<?= __('list_property_label_state') ?>">
                                    <option value=""><?= __('list_property_select_state') ?></option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?= (int)$state['id'] ?>"><?= htmlspecialchars($state['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="district_id" class="form-label fw-bold"><?= __('list_property_label_district') ?> *</label>
                                <select id="district_id" name="location" class="form-select" required disabled aria-label="<?= __('list_property_label_district') ?>">
                                    <option value=""><?= __('list_property_select_district_first') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-bold"><?= __('list_property_label_city') ?></label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="<?= __('list_property_ph_city') ?>" aria-label="<?= __('list_property_label_city') ?>" data-autofill="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label fw-bold"><?= __('list_property_label_pincode') ?></label>
                                <div class="input-group">
                                    <input type="text" name="pincode" id="pincode" class="form-control" placeholder="<?= __('list_property_ph_pincode') ?>" maxlength="6" inputmode="numeric" pattern="\d{6}" aria-label="<?= __('list_property_label_pincode') ?>" data-autofill="pincode">
                                    <button type="button" class="btn btn-outline-secondary" data-action="gps" title="Use My Location">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3 mt-4"><i class="fas fa-info-circle me-2 text-primary"></i><?= __('list_property_details_heading', null, 'Property details') ?></h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-bold"><?= __('list_property_label_price') ?> *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" name="price" id="price" class="form-control" placeholder="<?= __('list_property_ph_price') ?>" required inputmode="numeric" aria-label="<?= __('list_property_label_price') ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label fw-bold"><?= __('list_property_label_area') ?> *</label>
                                <input type="text" name="area" id="area" class="form-control" placeholder="<?= __('list_property_ph_area') ?>" required aria-label="<?= __('list_property_label_area') ?>">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="description" class="form-label fw-bold"><?= __('list_property_label_description') ?></label>
                            <textarea name="description" id="description" class="form-control" rows="3" maxlength="500" placeholder="<?= __('list_property_ph_description') ?>" data-aps-counter="#description_counter" aria-label="<?= __('list_property_label_description') ?>"></textarea>
                            <small id="description_counter" class="text-muted">0 / 500</small>
                            <button type="button" id="aiGenDesc" class="btn btn-sm mt-2" class="style-43547"><i class="fas fa-magic"></i> <?= __('ai_generate_description', null, 'Generate with AI') ?></button>
                        </div>
                    </div>

                    <div class="aps-cp-wizard-panel" data-panel="2">
                        <h4 class="mb-3"><i class="fas fa-camera me-2 text-primary"></i><?= __('list_property_photos_heading', null, 'Add photos of your property') ?></h4>
                        <div class="aps-cp-dropzone" id="property_image_dropzone">
                            <span class="aps-cp-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                            <p class="aps-cp-dropzone-text"><?= __('list_property_dropzone_text', null, 'Click to upload or drag images here') ?></p>
                            <p class="aps-cp-dropzone-hint"><?= __('list_property_image_hint') ?></p>
                            <input type="file" name="property_image" id="property_image" accept="image/jpeg,image/png,image/webp" data-aps-image-preview="#property_image_preview" data-max-files="5" aria-label="<?= __('list_property_label_image') ?>">
                        </div>
                        <div class="aps-cp-image-grid" id="property_image_preview"></div>

                        <div class="mt-2">
                            <label for="image_alt_text" class="form-label fw-bold"><?= __('list_property_alt_label', null, 'Image Alt Text (SEO)') ?></label>
                            <div class="input-group">
                                <input type="text" name="image_alt_text" id="image_alt_text" class="form-control" placeholder="<?= __('list_property_alt_ph', null, 'Auto-generate SEO alt text with AI') ?>" maxlength="160" aria-label="<?= __('list_property_alt_label', null, 'Image Alt Text') ?>">
                                <button type="button" id="aiGenAlt" class="btn btn-sm" class="style-43547"><i class="fas fa-magic"></i> <?= __('ai_alt_text', null, 'AI Alt') ?></button>
                            </div>
                            <small class="text-muted"><?= __('list_property_alt_hint', null, 'Improves accessibility & Google image search ranking.') ?></small>
                        </div>

                        <h4 class="mb-3 mt-4"><i class="fas fa-user-circle me-2 text-primary"></i><?= __('list_property_contact_heading', null, 'Contact details') ?></h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold"><?= __('list_property_label_name') ?> *</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="<?= __('list_property_ph_name') ?>" required value="<?= htmlspecialchars($userName ?? '') ?>" aria-label="<?= __('list_property_label_name') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold"><?= __('list_property_label_phone') ?> *</label>
                                <input type="tel" name="phone" id="phone" class="form-control" placeholder="<?= __('list_property_ph_phone') ?>" required inputmode="tel" value="<?= htmlspecialchars($userPhone ?? '') ?>" aria-label="<?= __('list_property_label_phone') ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="email" class="form-label fw-bold"><?= __('list_property_label_email') ?></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="<?= __('list_property_ph_email') ?>" value="<?= htmlspecialchars($userEmail ?? '') ?>" aria-label="<?= __('list_property_label_email') ?>">
                        </div>

                        <?php if (!$isLoggedIn): ?>
                            <div class="aps-cp-info-card mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong><?= __('list_property_guest_label') ?>:</strong> <?= __('list_property_guest_desc') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="aps-cp-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary" data-wizard-prev disabled>
                        <i class="fas fa-arrow-left me-1"></i><?= __('back', null, 'Back') ?>
                    </button>
                    <div class="aps-cp-wizard-progress" aria-label="Progress">
                        <div class="aps-cp-wizard-progress-bar" class="style-12552"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-wizard-next>
                            <?= __('next', null, 'Next') ?> <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                        <?php if ($isLoggedIn): ?>
                            <button type="submit" class="btn btn-success" data-wizard-submit class="style-24280">
                                <i class="fas fa-paper-plane me-1"></i><?= __('list_property_button_submit') ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" data-wizard-submit class="style-24280" id="guestSubmitBtn">
                                <i class="fas fa-paper-plane me-1"></i><?= __('list_property_button_submit') ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted mt-3 small">
                <?= __('list_property_terms_prefix') ?> <a href="<?= BASE_URL ?>/terms"><?= __('list_property_terms_link') ?></a>
            </p>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="aps-cp-card mb-3">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-star"></i><?= __('list_property_free_title') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <ul class="list-unstyled mb-0 aps-cp-checklist">
                    <li><i class="fas fa-check text-success"></i> <?= __('list_property_free_1') ?></li>
                    <li><i class="fas fa-check text-success"></i> <?= __('list_property_free_2') ?></li>
                    <li><i class="fas fa-check text-success"></i> <?= __('list_property_free_3') ?></li>
                    <li><i class="fas fa-check text-success"></i> <?= __('list_property_free_4') ?></li>
                </ul>
            </div>
        </div>

        <div class="aps-cp-card mb-3">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-users"></i><?= __('list_property_benefits_title') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-phone text-primary me-2"></i><?= __('list_property_benefit_1') ?></li>
                    <li class="mb-2"><i class="fas fa-gavel text-primary me-2"></i><?= __('list_property_benefit_2') ?></li>
                    <li class="mb-2"><i class="fas fa-hand-holding-usd text-primary me-2"></i><?= __('list_property_benefit_3') ?></li>
                    <li><i class="fas fa-user-friends text-primary me-2"></i><?= __('list_property_benefit_4') ?></li>
                </ul>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-body text-center">
                <div class="aps-cp-empty-icon mb-3">
                    <i class="fas fa-headset"></i>
                </div>
                <h5 class="mb-2"><?= __('list_property_help_title') ?></h5>
                <p class="text-muted mb-3"><?= __('list_property_help_desc') ?></p>
                <div class="d-grid gap-2">
                    <a href="tel:<?= $phoneRaw ?>" class="btn btn-success">
                        <i class="fas fa-phone me-2"></i><?= __('list_property_call_label') ?>: <?= $phoneDisplay ?>
                    </a>
                    <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I want to list my property for sale" target="_blank" rel="noopener" class="btn btn-outline-success">
                        <i class="fab fa-whatsapp me-2"></i><?= __('list_property_whatsapp_button') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // ----- Sell / Rent pill toggle -----
    var listingInput = document.getElementById('listing_type');
    var pills = document.querySelectorAll('[data-pill]');
    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            pills.forEach(function(p) {
                p.classList.remove('is-active');
                p.setAttribute('aria-pressed', 'false');
            });
            pill.classList.add('is-active');
            pill.setAttribute('aria-pressed', 'true');
            listingInput.value = pill.getAttribute('data-pill');
        });
    });

    // ----- Type option highlight -----
    var typeOptions = document.querySelectorAll('.aps-cp-type-option input[type="radio"]');
    typeOptions.forEach(function(r) {
        r.addEventListener('change', function() {
            typeOptions.forEach(function(o) { o.closest('.aps-cp-type-option').classList.remove('is-selected'); });
            if (r.checked) r.closest('.aps-cp-type-option').classList.add('is-selected');
        });
    });

    // ----- Location cascade (state -> district) -----
    var stateSelect = document.getElementById('state_id');
    var districtSelect = document.getElementById('district_id');
    var selectedStateId = document.getElementById('selected_state_id');
    var selectedDistrictId = document.getElementById('selected_district_id');
    var selectedCityName = document.getElementById('selected_city_name');

    if (stateSelect && districtSelect) {
        stateSelect.addEventListener('change', async function() {
            var stateId = this.value;
            if (!stateId) {
                districtSelect.innerHTML = '<option value=""><?= addslashes(__("select_state_first")) ?>...</option>';
                districtSelect.disabled = true;
                return;
            }
            districtSelect.disabled = true;
            districtSelect.innerHTML = '<option value=""><?= addslashes(__("page_loading")) ?></option>';
            try {
                var resp = await fetch(window.BASE_URL + '/api/locations/districts?state_id=' + encodeURIComponent(stateId), { credentials: 'same-origin' });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                var districts = await resp.json();
                districtSelect.innerHTML = '<option value=""><?= addslashes(__("select_district_dotdot")) ?></option>';
                if (Array.isArray(districts)) {
                    districts.forEach(function(d) {
                        var opt = document.createElement('option');
                        opt.value = d.name;
                        opt.dataset.id = d.id;
                        opt.textContent = d.name;
                        districtSelect.appendChild(opt);
                    });
                }
                districtSelect.disabled = false;
            } catch (err) {
                console.error('District load failed:', err);
                districtSelect.innerHTML = '<option value=""><?= addslashes(__("error_loading")) ?></option>';
            }
        });

        districtSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            selectedStateId.value = stateSelect.value;
            selectedDistrictId.value = selected && selected.dataset ? (selected.dataset.id || '') : '';
            selectedCityName.value = this.value;
        });
    }

    // ----- Pincode auto-fill -----
    var pincodeInput = document.getElementById('pincode');
    var cityInput = document.getElementById('city');
    if (pincodeInput) {
        var pincodeTimer;
        pincodeInput.addEventListener('input', function() {
            clearTimeout(pincodeTimer);
            pincodeTimer = setTimeout(async function() {
                var pin = pincodeInput.value.trim();
                if (pin.length !== 6 || !/^\d+$/.test(pin)) return;
                try {
                    var resp = await fetch(window.BASE_URL + '/api/locations/pincode/' + encodeURIComponent(pin), { credentials: 'same-origin' });
                    if (!resp.ok) return;
                    var data = await resp.json();
                    if (data && data.found && data.data) {
                        if (cityInput && data.data.city) cityInput.value = data.data.city;
                        if (data.data.district && districtSelect) {
                            for (var i = 0; i < districtSelect.options.length; i++) {
                                if (districtSelect.options[i].textContent.indexOf(data.data.district) !== -1) {
                                    districtSelect.value = districtSelect.options[i].value;
                                    districtSelect.dispatchEvent(new Event('change'));
                                    break;
                                }
                            }
                        }
                    }
                } catch (e) { /* silent */ }
            }, 500);
        });
    }

    // ----- Guest submit: redirect to quick register modal -----
    var guestSubmit = document.getElementById('guestSubmitBtn');
    if (guestSubmit) {
        guestSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            var nameInput = document.getElementById('name');
            var phoneInput = document.getElementById('phone');
            var emailInput = document.getElementById('email');
            if (!nameInput.value.trim() || !phoneInput.value.trim()) {
                if (window.APS && APS.toast) APS.toast('Please fill in your name and phone number first', 'warning');
                return;
            }
            var qrName = document.getElementById('qrName');
            var qrPhone = document.getElementById('qrPhone');
            var qrEmail = document.getElementById('qrEmail');
            if (qrName) qrName.value = nameInput.value;
            if (qrPhone) qrPhone.value = phoneInput.value;
            if (qrEmail) qrEmail.value = emailInput ? emailInput.value : '';
            var qrReferral = document.getElementById('qrReferralCode');
            if (qrReferral) qrReferral.value = '';
            var modalEl = document.getElementById('quickRegisterModal');
            if (modalEl && window.bootstrap) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                // Fallback: submit form directly
                document.getElementById('listPropertyForm').submit();
            }
        });
    }
})();
</script>

<!-- Smart Registration Behavior Tracking -->
<script>
(function() {
    var token = (document.cookie.match('(^|;)\\s*smart_reg_token\\s*=\\s*([^;]+)') || [])[2];
    if (!token) return;
    function track(type, data) {
        try {
            var x = new XMLHttpRequest();
            x.open('POST', '<?= BASE_URL ?>/api/smart-register/track', true);
            x.setRequestHeader('Content-Type', 'application/json');
            x.send(JSON.stringify({ token: token, event_type: type, event_data: data || null, page_url: window.location.href }));
        } catch (e) { console.error("Error:", e); }
    }
    track('page_view', { action: 'list_property_page' });
})();
</script>

<?php include __DIR__ . '/../components/quick_register_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('aiGenDesc');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var fd = new FormData();
        fd.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
        fd.append('name', document.getElementById('name') ? document.getElementById('name').value : '');
        fd.append('location', document.getElementById('city') ? document.getElementById('city').value : '');
        fd.append('price', document.getElementById('price') ? document.getElementById('price').value : '');
        fd.append('area_sqft', document.getElementById('area') ? document.getElementById('area').value : '');
        fd.append('type', (document.querySelector('input[name="property_type"]:checked') || {}).value || 'plot');
        var ta = document.getElementById('description');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        fetch('<?= BASE_URL ?>/ai/content/description', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success && d.description) {
                    ta.value = d.description.substring(0, 500);
                    var c = document.getElementById('description_counter');
                    if (c) c.textContent = ta.value.length + ' / 500';
                } else {
                    alert('AI generation failed. Please try again.');
                }
            })
            .catch(function () { alert('AI generation failed. Please try again.'); })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> <?= __('ai_generate_description', null, 'Generate with AI') ?>';
            });
    });

    var altBtn = document.getElementById('aiGenAlt');
    if (altBtn) {
        altBtn.addEventListener('click', function () {
            var fd = new FormData();
            fd.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
            var fileInput = document.getElementById('property_image');
            var fileName = (fileInput && fileInput.files && fileInput.files.length > 0) ? fileInput.files[0].name : '';
            fd.append('filename', fileName);
            fd.append('title', document.getElementById('name') ? document.getElementById('name').value : '');
            fd.append('type', (document.querySelector('input[name="property_type"]:checked') || {}).value || 'plot');
            fd.append('location', document.getElementById('city') ? document.getElementById('city').value : '');
            var altInput = document.getElementById('image_alt_text');
            altBtn.disabled = true;
            altBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
            fetch('<?= BASE_URL ?>/ai/content/image-tags', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.success && d.alt_text) {
                        altInput.value = d.alt_text.substring(0, 160);
                    } else {
                        alert('AI alt-text generation failed. Please try again.');
                    }
                })
                .catch(function () { alert('AI alt-text generation failed. Please try again.'); })
                .finally(function () {
                    altBtn.disabled = false;
                    altBtn.innerHTML = '<i class="fas fa-magic"></i> <?= __('ai_alt_text', null, 'AI Alt') ?>';
                });
        });
    }
});
</script>
