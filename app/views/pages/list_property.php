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

// Page-scoped assets: the public layout does NOT load aps-components.css /
// customer-pages.js, so the wizard + pickers ship their own styles + behavior.
$extraHead = ($extraHead ?? '')
    . '<link href="' . BASE_URL . '/assets/css/consolidated/aps-components.css?v=2" rel="stylesheet">'
    . <<<'LPHEAD'
<style>
.lp-hero{position:relative;background:linear-gradient(120deg,#312e81 0%,#6d28d9 55%,#9333ea 100%);color:#fff;overflow:hidden}
.lp-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(600px 300px at 85% 10%,rgba(255,255,255,.18),transparent 60%),radial-gradient(500px 260px at 5% 95%,rgba(0,0,0,.25),transparent 60%);pointer-events:none}
.lp-hero .container{position:relative;z-index:1}
.lp-eyebrow{display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.35);color:#fff;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:.4rem .9rem;border-radius:999px;margin-bottom:1rem}
.lp-hero h1{font-weight:800;letter-spacing:-.01em;margin-bottom:.6rem}
.lp-lead{color:rgba(255,255,255,.85);font-size:1.08rem;max-width:34rem}
.lp-stats{display:flex;gap:1.4rem;flex-wrap:wrap;margin-top:1.2rem}
.lp-stat{display:flex;align-items:center;gap:.6rem}
.lp-stat i{width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);display:inline-flex;align-items:center;justify-content:center;font-size:1rem}
.lp-stat b{display:block;font-size:.95rem;line-height:1.1}
.lp-stat small{color:rgba(255,255,255,.75);font-size:.75rem}
.lp-cta-card{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.3);border-radius:18px;padding:1.4rem;backdrop-filter:blur(6px)}
.lp-cta-card .btn-call{background:#fbbf24;border-color:#fbbf24;color:#3b2f04;font-weight:800}
.lp-cta-card .btn-call:hover{background:#f59e0b;border-color:#f59e0b;color:#fff}
.lp-container{padding-top:1.6rem;padding-bottom:2.5rem}
.aps-cp-wizard{scroll-margin-top:90px}
.lp-sticky{position:sticky;top:90px}
.lp-steps{list-style:none;margin:0;padding:0;counter-reset:lpstep;display:flex;flex-direction:column;gap:.9rem}
.lp-steps li{position:relative;padding-left:2.9rem}
.lp-steps li::before{counter-increment:lpstep;content:counter(lpstep);position:absolute;left:0;top:0;width:2.1rem;height:2.1rem;border-radius:50%;background:rgba(79,70,229,.12);color:#4f46e5;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:.9rem}
.lp-steps li::after{content:'';position:absolute;left:1.02rem;top:2.3rem;bottom:-.7rem;width:2px;background:#e2e8f0}
.lp-steps li:last-child::after{display:none}
.lp-steps b{display:block;font-size:.92rem}
.lp-steps small{color:#64748b}
.btn-ai-generate{background:linear-gradient(135deg,#667eea,#764ba2);border:none;color:#fff;font-weight:600;white-space:nowrap}
.btn-ai-generate:hover{color:#fff;filter:brightness(1.08)}
.btn-ai-generate:disabled{opacity:.65}
.aps-cp-type-option:focus-within{outline:2px solid #4f46e5;outline-offset:2px}
.aps-cp-wizard-step{cursor:pointer}
.was-validated .form-control:invalid,.form-control.is-invalid{border-color:#ef4444}
@media(max-width:575.98px){.lp-stats{gap:.9rem}.aps-cp-wizard-body{padding:1.1rem}.aps-cp-wizard-footer{flex-direction:column;align-items:stretch}.aps-cp-wizard-progress{max-width:none;margin:0}.lp-sticky{position:static}}
</style>
LPHEAD;
?>

<div class="lp-hero">
    <div class="container">
        <div class="row align-items-center g-4 py-4 py-lg-5">
            <div class="col-lg-7">
                <span class="lp-eyebrow"><i class="fas fa-bolt"></i><?= __('list_property_eyebrow', null, '100% Free &bull; No Commission') ?></span>
                <h1 class="display-6"><?= __('list_property_hero_title') ?></h1>
                <p class="lp-lead mb-0"><?= __('list_property_hero_lead') ?></p>
                <div class="lp-stats">
                    <div class="lp-stat"><i class="fas fa-stopwatch"></i><div><b><?= __('list_property_stat_time', null, '1 Minute') ?></b><small><?= __('list_property_stat_time_sub', null, 'to list your property') ?></small></div></div>
                    <div class="lp-stat"><i class="fas fa-badge-check"></i><div><b><?= __('list_property_stat_leads', null, 'Verified Buyers') ?></b><small><?= __('list_property_stat_leads_sub', null, 'genuine enquiries only') ?></small></div></div>
                    <div class="lp-stat"><i class="fas fa-headset"></i><div><b><?= __('list_property_stat_help', null, 'Free Assistance') ?></b><small><?= __('list_property_stat_help_sub', null, 'help with photos & price') ?></small></div></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="lp-cta-card">
                    <h5 class="fw-bold mb-1"><i class="fas fa-phone-volume me-2"></i><?= __('list_property_cta_title', null, 'Prefer to talk to us?') ?></h5>
                    <p class="mb-3 small" style="color:rgba(255,255,255,.8)"><?= __('list_property_cta_desc', null, 'Our team will list the property for you on call.') ?></p>
                    <div class="d-grid gap-2">
                        <a href="tel:<?= $phoneRaw ?>" class="btn btn-call btn-lg"><i class="fas fa-phone me-2"></i><?= __('list_property_call_label') ?>: <?= $phoneDisplay ?></a>
                        <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I want to list my property" target="_blank" rel="noopener" class="btn btn-success btn-lg"><i class="fab fa-whatsapp me-2"></i><?= __('list_property_whatsapp_button') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container lp-container">

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
                    <li class="aps-cp-wizard-step active" data-step="0">
                        <span class="aps-cp-wizard-step-num"><span>1</span></span>
                        <span class="aps-cp-wizard-step-label"><?= __('list_property_step1_label', null, 'Type & Listing') ?></span>
                    </li>
                    <li class="aps-cp-wizard-step" data-step="1">
                        <span class="aps-cp-wizard-step-num"><span>2</span></span>
                        <span class="aps-cp-wizard-step-label"><?= __('list_property_step2_label', null, 'Location & Details') ?></span>
                    </li>
                    <li class="aps-cp-wizard-step" data-step="2">
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
                            <button type="button" id="aiGenDesc" class="btn btn-ai-generate btn-sm mt-2"><i class="fas fa-wand-magic-sparkles me-1"></i> <?= __('ai_generate_description', null, 'Generate with AI') ?></button>
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
                                <button type="button" id="aiGenAlt" class="btn btn-ai-generate btn-sm"><i class="fas fa-wand-magic-sparkles me-1"></i> <?= __('ai_alt_text', null, 'AI Alt') ?></button>
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
                    <div class="aps-cp-wizard-progress" role="progressbar" aria-valuemin="1" aria-valuemax="3" aria-valuenow="1" aria-label="<?= __('list_property_progress_label', null, 'Listing progress') ?>">
                        <div class="aps-cp-wizard-progress-bar"></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-wizard-next>
                            <?= __('next', null, 'Next') ?> <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                        <?php if ($isLoggedIn): ?>
                            <button type="button" class="btn btn-outline-primary" id="saveDraftBtn" title="<?= __('list_property_save_draft_title', null, 'Save as draft to continue later') ?>">
                                <i class="fas fa-save me-1"></i><?= __('list_property_save_draft', null, 'Save Draft') ?>
                            </button>
                            <button type="submit" class="btn btn-success" data-wizard-submit >
                                <i class="fas fa-paper-plane me-1"></i><?= __('list_property_button_submit') ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" data-wizard-submit id="guestSubmitBtn">
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
        <div class="lp-sticky">
        <div class="aps-cp-card mb-3">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-route"></i><?= __('list_property_how_title', null, 'How it works') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <ol class="lp-steps">
                    <li><b><?= __('list_property_how_1', null, 'Fill the 3-step form') ?></b><small><?= __('list_property_how_1_sub', null, 'Type, location, photos & contact — done in 1 minute') ?></small></li>
                    <li><b><?= __('list_property_how_2', null, 'Quick verification call') ?></b><small><?= __('list_property_how_2_sub', null, 'Our team confirms details & helps with price') ?></small></li>
                    <li><b><?= __('list_property_how_3', null, 'Get verified buyer calls') ?></b><small><?= __('list_property_how_3_sub', null, 'Your property goes live for genuine buyers') ?></small></li>
                </ol>
            </div>
        </div>

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
</div>
</div>

<script>
/* List-Property wizard: public layout does not load customer-pages.js,
   so steps / progress / photo preview / counters are wired here. */
(function() {
    'use strict';
    var wizard = document.querySelector('[data-aps-wizard]');
    if (wizard) {
        var panels = wizard.querySelectorAll('.aps-cp-wizard-panel');
        var steps = wizard.querySelectorAll('.aps-cp-wizard-step');
        var bar = wizard.querySelector('.aps-cp-wizard-progress-bar');
        var progress = wizard.querySelector('.aps-cp-wizard-progress');
        var prevBtn = wizard.querySelector('[data-wizard-prev]');
        var nextBtn = wizard.querySelector('[data-wizard-next]');
        var submitBtn = wizard.querySelector('[data-wizard-submit]');
        var current = 0, total = panels.length;
        function toast(msg, type) {
            if (window.APS && typeof APS.toast === 'function') APS.toast(msg, type || 'warning');
            else if (type === 'warning' || type === 'error') alert(msg);
        }
        function show(i) {
            if (i < 0 || i >= total) return;
            current = i;
            panels.forEach(function(p, k) { p.classList.toggle('active', k === i); });
            steps.forEach(function(s, k) {
                s.classList.remove('active', 'completed');
                if (k < i) s.classList.add('completed');
                else if (k === i) s.classList.add('active');
            });
            if (bar) bar.style.width = (((i + 1) / total) * 100) + '%';
            if (progress) progress.setAttribute('aria-valuenow', String(i + 1));
            if (prevBtn) prevBtn.disabled = (i === 0);
            if (nextBtn) nextBtn.style.display = (i === total - 1) ? 'none' : '';
            if (submitBtn) submitBtn.style.display = (i === total - 1) ? '' : 'none';
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        function validPanel(panel) {
            var ok = true;
            panel.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(f) {
                var v = (f.type === 'radio' || f.type === 'checkbox')
                    ? wizard.querySelector('input[name="' + f.name + '"]:checked')
                    : f.value.trim();
                f.classList.remove('is-invalid');
                if (!v) { f.classList.add('is-invalid'); ok = false; }
            });
            var phone = panel.querySelector('#phone');
            if (phone && phone.value.trim() !== '' && !/^[6-9]\d{9}$/.test(phone.value.replace(/\D/g, '').slice(-10))) {
                phone.classList.add('is-invalid'); ok = false;
                toast('Please enter a valid 10-digit mobile number', 'warning');
                return false;
            }
            return ok;
        }
        if (nextBtn) nextBtn.addEventListener('click', function() {
            if (validPanel(panels[current])) show(current + 1);
            else toast('Please fill all required fields in this step');
        });
        if (prevBtn) prevBtn.addEventListener('click', function() { show(current - 1); });
        steps.forEach(function(s, k) {
            s.addEventListener('click', function() {
                if (k <= current) show(k);
                else if (k === current + 1 && validPanel(panels[current])) show(k);
                else if (k > current + 1) toast('Please complete the current step first');
            });
        });
        show(0);
    }

    // ----- Photo preview + drag-drop (mirrors customer-pages.js) -----
    var fileInput = document.getElementById('property_image');
    var preview = document.getElementById('property_image_preview');
    var dropzone = document.getElementById('property_image_dropzone');
    if (fileInput && preview) {
        var files = [], MAX = 5;
        function render() {
            preview.innerHTML = '';
            files.forEach(function(item, idx) {
                var thumb = document.createElement('div');
                thumb.className = 'aps-cp-image-thumb' + (idx === 0 ? ' is-primary' : '');
                var img = document.createElement('img');
                img.src = item.url; img.alt = item.name; img.loading = 'lazy';
                var rm = document.createElement('button');
                rm.type = 'button'; rm.className = 'aps-cp-image-thumb-remove';
                rm.setAttribute('aria-label', 'Remove photo');
                rm.innerHTML = '<i class="fas fa-times"></i>';
                rm.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); files.splice(idx, 1); sync(); render(); });
                thumb.appendChild(img); thumb.appendChild(rm); preview.appendChild(thumb);
            });
        }
        function sync() {
            try {
                var dt = new DataTransfer();
                files.forEach(function(it) { dt.items.add(it.file); });
                fileInput.files = dt.files;
            } catch (e) { /* older browsers: keep input as-is */ }
        }
        function add(list) {
            Array.from(list || []).forEach(function(f) {
                if (files.length >= MAX) return;
                if (['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1) return;
                if (f.size > 5 * 1024 * 1024) return;
                (function(file) {
                    var r = new FileReader();
                    r.onload = function(e) { files.push({ file: file, name: file.name, url: e.target.result }); sync(); render(); };
                    r.readAsDataURL(file);
                })(f);
            });
        }
        fileInput.addEventListener('change', function() { add(fileInput.files); });
        if (dropzone) {
            dropzone.addEventListener('click', function(e) { if (e.target.tagName !== 'INPUT') fileInput.click(); });
            ['dragenter', 'dragover'].forEach(function(ev) { dropzone.addEventListener(ev, function(e) { e.preventDefault(); dropzone.classList.add('is-dragging'); }); });
            ['dragleave', 'drop'].forEach(function(ev) { dropzone.addEventListener(ev, function(e) { e.preventDefault(); dropzone.classList.remove('is-dragging'); }); });
            dropzone.addEventListener('drop', function(e) { e.preventDefault(); add(e.dataTransfer.files); });
        }
    }

    // ----- Live description counter (data-aps-counter has no public handler) -----
    var desc = document.getElementById('description');
    var counter = document.getElementById('description_counter');
    if (desc && counter) desc.addEventListener('input', function() { counter.textContent = desc.value.length + ' / 500'; });

    // ----- Indian price grouping: display 1,50,000 but submit raw digits -----
    var price = document.getElementById('price');
    var form = document.getElementById('listPropertyForm');
    function inr(n) {
        var s = String(n).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        if (!s) return '';
        var last3 = s.slice(-3), rest = s.slice(0, -3);
        if (rest) last3 = ',' + last3;
        return rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + last3;
    }
    if (price) {
        price.addEventListener('input', function() {
            var pos = price.selectionStart, before = price.value.length;
            price.value = inr(price.value);
            pos += price.value.length - before;
            try { price.setSelectionRange(pos, pos); } catch (e) {}
        });
        price.addEventListener('blur', function() { price.value = inr(price.value); });
    }
    if (form) {
        form.addEventListener('submit', function() {
            if (price) price.value = price.value.replace(/\D/g, '');
            var btn = form.querySelector('[data-wizard-submit]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
                setTimeout(function() { btn.disabled = false; }, 8000);
            }
        });
    }
})();
</script>

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

    // ----- Listing draft: persist across sessions (localStorage + 7-day expiry) -----
    var DRAFT_KEY = 'lp_draft_v1';
    var DRAFT_TTL = 7 * 24 * 60 * 60 * 1000; // 7 days
    function stashDraft() {
        try {
            var data = {};
            document.querySelectorAll('#listPropertyForm input, #listPropertyForm select, #listPropertyForm textarea').forEach(function(el) {
                if (!el.name || el.type === 'file') return;
                if ((el.type === 'radio' || el.type === 'checkbox')) { if (el.checked) data[el.name] = el.value; return; }
                data[el.name] = el.value;
            });
            var payload = { data: data, ts: Date.now() };
            localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
        } catch (e) { /* storage unavailable */ }
    }
    function restoreDraft() {
        var raw = null;
        try { raw = localStorage.getItem(DRAFT_KEY); } catch (e) { return false; }
        if (!raw) return false;
        var restored = false;
        try {
            var payload = JSON.parse(raw);
            if (payload.ts && Date.now() - payload.ts > DRAFT_TTL) { clearDraft(); return false; }
            var data = payload.data || payload; // backward compat
            Object.keys(data).forEach(function(name) {
                var els = document.querySelectorAll('#listPropertyForm [name="' + name + '"]');
                if (!els.length) return;
                if (els[0].type === 'radio') {
                    els.forEach(function(r) {
                        if (r.value === data[name]) {
                            r.checked = true;
                            r.closest('.aps-cp-type-option') && r.closest('.aps-cp-type-option').classList.add('is-selected');
                        } else if (r.closest('.aps-cp-type-option')) {
                            r.closest('.aps-cp-type-option').classList.remove('is-selected');
                        }
                    });
                    restored = true;
                    return;
                }
                if (!els[0].value) { els[0].value = data[name]; restored = true; }
            });
            if (restored && window.APS && APS.toast) APS.toast('Your previous details were restored — just hit Submit', 'info');
        } catch (e) { /* corrupt draft */ }
        return restored;
    }
    function clearDraft() { try { localStorage.removeItem(DRAFT_KEY); } catch (e) {} }
    // Auto-stash on every input change (debounced)
    var stashTimer;
    document.querySelectorAll('#listPropertyForm input, #listPropertyForm select, #listPropertyForm textarea').forEach(function(el) {
        if (el.type === 'file') return;
        el.addEventListener('input', function() {
            clearTimeout(stashTimer);
            stashTimer = setTimeout(stashDraft, 800);
        });
        el.addEventListener('change', stashDraft); // for selects/radios
    });
    restoreDraft();
    var lpForm = document.getElementById('listPropertyForm');
    if (lpForm) lpForm.addEventListener('submit', function() { clearDraft(); });

    // ----- Guest submit: stash draft, then register via modal -----
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
            stashDraft();
            var qrName = document.getElementById('qrName');
            var qrPhone = document.getElementById('qrPhone');
            var qrEmail = document.getElementById('qrEmail');
            if (qrName) qrName.value = nameInput.value;
            if (qrPhone) qrPhone.value = phoneInput.value;
            if (qrEmail) qrEmail.value = emailInput ? emailInput.value : '';
            var qrReferral = document.getElementById('qrReferralCode');
            if (qrReferral) qrReferral.value = '';
            // After successful registration the session exists -> submit the listing
            window.__qrAfterSuccess = function() {
                window.__qrAfterSuccess = null;
                clearDraft();
                document.getElementById('listPropertyForm').submit();
            };
            var modalEl = document.getElementById('quickRegisterModal');
            if (modalEl && window.bootstrap) {
                if (typeof resetQuickRegisterModal === 'function') resetQuickRegisterModal();
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                // Fallback: submit form directly
                document.getElementById('listPropertyForm').submit();
            }
        });
    }

    // ----- Save Draft (logged-in users) -----
    var saveDraftBtn = document.getElementById('saveDraftBtn');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var form = document.getElementById('listPropertyForm');
            var fd = new FormData(form);
            fd.append('action', 'save_draft');
            var originalHtml = saveDraftBtn.innerHTML;
            saveDraftBtn.disabled = true;
            saveDraftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            fetch((window.BASE_URL || '<?= BASE_URL ?>') + '/list-property/save-draft', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    clearDraft(); // draft saved server-side, clear local
                    if (window.APS && APS.toast) APS.toast(d.message || 'Draft saved successfully', 'success');
                } else {
                    if (window.APS && APS.toast) APS.toast(d.message || 'Failed to save draft', 'error');
                }
            })
            .catch(function() {
                if (window.APS && APS.toast) APS.toast('Network error', 'error');
            })
            .finally(function() {
                saveDraftBtn.disabled = false;
                saveDraftBtn.innerHTML = originalHtml;
            });
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
                btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> <?= __('ai_generate_description', null, 'Generate with AI') ?>';
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
                    altBtn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> <?= __('ai_alt_text', null, 'AI Alt') ?>';
                });
        });
    }
});
</script>
