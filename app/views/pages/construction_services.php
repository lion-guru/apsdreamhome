<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<style>
:root { --primary: #0d9488; --secondary: #ff6f00; --accent: #00c853; }

.service-card { border: none; border-radius: 16px; transition: all 0.3s ease; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; }
.service-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
.service-card .card-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 1rem; }
.process-step { text-align: center; padding: 2rem 1rem; }
.process-step .step-num { width: 50px; height: 50px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700; margin: 0 auto 1rem; }
.project-card { border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s; }
.project-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
.project-card .card-img-top { height: 200px; object-fit: cover; }
.contact-form-section { background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); }
.flash-message { position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash-message alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php elseif (isset($_SESSION['flash_error'])): ?>
    <div class="flash-message alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="hero-premium pt-5 pb-5">
    <div class="container position-relative premium-reveal fade-up z-2">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="capsule-badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 mb-3 px-3 py-2"><i class="fas fa-hard-hat me-1"></i> <?= __('const_iso_badge') ?></span>
                <h1 class="display-4 fw-bold text-white mb-4"><?= __('const_hero_title') ?><br><span class="text-warning"><?= __('const_hero_subtitle') ?></span></h1>
                <p class="lead text-white-50 mb-4 fs-5"><?= __('const_hero_desc') ?></p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-form" class="btn btn-premium px-4 py-2"><i class="fas fa-building me-2"></i><?= __('const_get_quote') ?></a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-list me-2"></i><?= __('const_our_services') ?></a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4"><h3 class="text-warning mb-0">50+</h3><small class="text-white-50"><?= __('const_projects_completed') ?></small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">15+</h3><small class="text-white-50"><?= __('const_years_exp') ?></small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">1000+</h3><small class="text-white-50"><?= __('const_happy_clients') ?></small></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="position-relative">
                    <img loading="lazy" src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=600&h=400&q=80" alt="Construction" class="img-fluid rounded-4 shadow-lg glass-panel p-2">
                    <div class="position-absolute bottom-0 start-0 bg-white text-dark p-3 rounded-3 m-3 shadow">
                        <i class="fas fa-check-circle text-success me-1"></i> <?= __('const_iso_badge') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<nav class="bg-white border-bottom shadow-sm" aria-label="breadcrumb">
    <div class="container py-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('colony_breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item active"><?= __('const_nav') ?></li>
        </ol>
    </div>
</nav>

<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2"><?= __('const_services_badge') ?></span>
            <h2 class="display-5 fw-bold"><?= __('const_services_heading') ?></h2>
            <p class="lead text-muted"><?= __('const_services_desc') ?></p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-home"></i></div>
                    <h4><?= __('const_residential') ?></h4>
                    <p class="text-muted"><?= __('const_residential_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_res_custom') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_res_villa') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_res_apartment') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_res_renovation') ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-building"></i></div>
                    <h4><?= __('const_commercial') ?></h4>
                    <p class="text-muted"><?= __('const_commercial_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_com_office') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_com_retail') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_com_shopping') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_com_industrial') ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-success bg-opacity-10 text-success"><i class="fas fa-drafting-compass"></i></div>
                    <h4><?= __('const_architectural') ?></h4>
                    <p class="text-muted"><?= __('const_architectural_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_arch_plans') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_arch_3d') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_arch_structural') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_arch_vastu') ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-road"></i></div>
                    <h4><?= __('const_infrastructure') ?></h4>
                    <p class="text-muted"><?= __('const_infrastructure_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_infra_road') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_infra_drainage') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_infra_water') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_infra_community') ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-info bg-opacity-10 text-info"><i class="fas fa-tools"></i></div>
                    <h4><?= __('const_renovation') ?></h4>
                    <p class="text-muted"><?= __('const_renovation_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_reno_home') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_reno_office') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_reno_structural') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_reno_waterproof') ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-handshake"></i></div>
                    <h4><?= __('const_turnkey') ?></h4>
                    <p class="text-muted"><?= __('const_turnkey_desc') ?></p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_turnkey_management') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_turnkey_material') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_turnkey_labour') ?></li>
                        <li><i class="fas fa-check text-success me-1"></i> <?= __('const_turnkey_quality') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2"><?= __('const_process_badge') ?></span>
            <h2 class="display-5 fw-bold"><?= __('const_process_heading') ?></h2>
        </div>
        <div class="row">
            <div class="col-md-3"><div class="process-step"><div class="step-num">1</div><h5><?= __('const_step1_title') ?></h5><p class="text-muted small"><?= __('const_step1_desc') ?></p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">2</div><h5><?= __('const_step2_title') ?></h5><p class="text-muted small"><?= __('const_step2_desc') ?></p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">3</div><h5><?= __('const_step3_title') ?></h5><p class="text-muted small"><?= __('const_step3_desc') ?></p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">4</div><h5><?= __('const_step4_title') ?></h5><p class="text-muted small"><?= __('const_step4_desc') ?></p></div></div>
        </div>
    </div>
</section>

<section id="projects" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2"><?= __('const_our_work') ?></span>
            <h2 class="display-5 fw-bold"><?= __('const_recent_projects') ?></h2>
            <p class="lead text-muted"><?= __('const_recent_projects_desc') ?></p>
        </div>
        <div class="row g-4">
<?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $idx => $p): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="project-card card" data-aos="zoom-in" data-aos-delay="<?= $idx * 100 ?>">
                                <?php
                                $imgSrc = '';
                                if (!empty($p['image'])) {
                                    $imgSrc = (filter_var($p['image'], FILTER_VALIDATE_URL))
                                        ? $p['image']
                                        : BASE_URL . '/' . ltrim($p['image'], '/');
                                }
                                ?>
                                <?php if (!empty($imgSrc)): ?>
                                    <img src="<?= htmlspecialchars($imgSrc ?? '');?>" class="card-img-top" alt="<?= htmlspecialchars($p['site_name'] ?? 'Project') ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"><i class="fas fa-building fa-4x text-primary opacity-50"></i></div>
                                <?php endif; ?>
                                <div class="card-body aps-cp-card-body">
                                    <span class="badge <?= $p['status'] === 'completed' ? 'bg-success' : 'bg-warning' ?> mb-2"><?= ucfirst($p['status'] ?? 'In Progress') ?></span>
                                    <h5 class="fw-bold"><?= htmlspecialchars($p['site_name'] ?? 'Project') ?></h5>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($p['location'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-hard-hat fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted"><?= __('const_coming_soon') ?></h4>
                    <p class="text-muted"><?= __('const_coming_soon_desc') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="contact-form" class="contact-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2"><?= __('const_get_started') ?></span>
                    <h2 class="display-5 fw-bold"><?= __('const_quote_heading') ?></h2>
                    <p class="lead text-muted"><?= __('const_quote_desc') ?></p>
                </div>
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
<form action="<?= BASE_URL ?>/construction-services/inquiry" method="POST" id="constructionInquiryForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('contact_form_name') ?> <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('contact_form_phone') ?> <span class="text-danger">*</span></label><input type="tel" name="phone" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('contact_form_email') ?></label><input type="email" name="email" class="form-control form-control-lg"></div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('const_project_type') ?></label>
                                    <select name="project_type" class="form-select form-select-lg" required>
                                        <option value=""><?= __('const_select') ?></option>
                                        <option value="residential"><?= __('const_type_residential') ?></option>
                                        <option value="commercial"><?= __('const_type_commercial') ?></option>
                                        <option value="renovation"><?= __('const_type_renovation') ?></option>
                                        <option value="infrastructure"><?= __('const_type_infrastructure') ?></option>
                                        <option value="turnkey"><?= __('const_type_turnkey') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('const_budget_range') ?></label><input type="number" name="budget" class="form-control form-control-lg" placeholder="<?= __('const_budget_placeholder') ?>" min="100000"></div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('const_location') ?></label><input type="text" name="location" class="form-control form-control-lg" placeholder="<?= __('const_location_placeholder') ?>" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('const_timeline') ?></label>
                                    <select name="timeline" class="form-select form-select-lg">
                                        <option value=""><?= __('const_select_timeline') ?></option>
                                        <option value="1-3"><?= __('const_timeline_1_3') ?></option>
                                        <option value="3-6"><?= __('const_timeline_3_6') ?></option>
                                        <option value="6-12"><?= __('const_timeline_6_12') ?></option>
                                        <option value="12+"><?= __('const_timeline_12_plus') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-medium"><?= __('const_plot_area') ?></label><input type="number" name="plot_area" class="form-control form-control-lg" placeholder="<?= __('const_plot_area_placeholder') ?>" min="100"></div>
                                <div class="col-12"><label class="form-label fw-medium"><?= __('const_project_details') ?></label><textarea name="message" rows="4" class="form-control" placeholder="<?= __('const_project_details_placeholder') ?>" required></textarea></div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane me-2"></i><?= __('const_submit_inquiry') ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-4 g-3 text-center">
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-phone-alt text-primary fa-2x mb-2"></i><h6><?= __('const_call_us') ?></h6><p class="mb-0 text-muted"><?= $phoneDisplay ?></p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-envelope text-primary fa-2x mb-2"></i><h6><?= __('const_email') ?></h6><p class="mb-0 text-muted"><?= $sc('contact_email', 'info@apsdreamhome.com') ?></p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-map-marker-alt text-primary fa-2x mb-2"></i><h6><?= __('const_office') ?></h6><p class="mb-0 text-muted">Gorakhpur, UP</p></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-dark text-white text-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-md-start">
                <h4 class="mb-1"><?= __('const_cta_title') ?></h4>
                <p class="mb-0 opacity-75"><?= __('const_cta_desc') ?></p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg px-4"><i class="fas fa-phone me-2"></i><?= __('const_call_now') ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Construction Inquiry Modal -->
<div class="modal fade" id="constModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 p-4 bg-gradient-warning" id="constModalHeader">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-hard-hat text-white fa-xl" id="constModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-1" id="constModalTitle">
                            <?= __('const_get_quote') ?></h5>
                        <p class="text-white-50 small mb-0" id="constModalSubtitle">
                            Get a free construction estimate for your project</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="constModalBody">
            </div>
        </div>
    </div>
</div>

<script>
function openConstModal() {
    var header = document.getElementById('constModalHeader');
    var icon = document.getElementById('constModalIcon');
    var title = document.getElementById('constModalTitle');
    var subtitle = document.getElementById('constModalSubtitle');
    var body = document.getElementById('constModalBody');

    header.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
    icon.className = 'fas fa-hard-hat text-white fa-xl';
    title.textContent = '<?= __('const_get_quote') ?>';
    subtitle.textContent = 'Get a free construction estimate for your project';

    var featuresHtml = 
        '<div class="row g-4"><div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Why Choose Us</h6><ul class="list-unstyled">';
    ['Free Site Visit', 'Detailed BOQ', 'Material Selection Help', 'Timeline Planning', 'Quality Assurance'].forEach(function(f) {
        featuresHtml += '<li class="mb-2"><i class="fas fa-check text-warning me-2"></i>' + f + '</li>';
    });
    featuresHtml += '</ul>';
    
    featuresHtml += '<div class="bg-light rounded p-3 mt-3"><h6 class="fw-bold mb-2">Construction Types</h6>';
    ['Residential', 'Commercial', 'Renovation', 'Infrastructure', 'Turnkey'].forEach(function(t) {
        featuresHtml += '<span class="badge bg-warning text-dark me-1 mb-1">' + t + '</span>';
    });
    featuresHtml += '</div>';
    
    featuresHtml += '</div><div class="col-md-5"><div class="card border-0 shadow-sm p-4 h-100">';
    featuresHtml += '<h5 class="fw-bold mb-3"><i class="fas fa-paper-plane text-warning me-2"></i>Quick Inquiry</h5>';
    featuresHtml += '<form id="constModalInquiryForm" class="small">';
    featuresHtml += '<input type="hidden" name="service_type" value="construction">';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Name *</label><input type="text" class="form-control form-control-sm" name="name" required></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Phone *</label><input type="tel" class="form-control form-control-sm" name="phone" required></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Email *</label><input type="email" class="form-control form-control-sm" name="email" required></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Project Type</label><select class="form-select form-control-sm" name="project_type"><option value="">Select</option><option value="residential">Residential</option><option value="commercial">Commercial</option><option value="renovation">Renovation</option><option value="infrastructure">Infrastructure</option><option value="turnkey">Turnkey</option></select></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Budget (₹)</label><input type="number" class="form-control form-control-sm" name="budget" min="100000"></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Location</label><input type="text" class="form-control form-control-sm" name="location" required></div>';
    featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1">Details</label><textarea class="form-control form-control-sm" name="message" rows="2" placeholder="Brief description..."></textarea></div>';
    featuresHtml += '<button type="submit" class="btn btn-sm btn-warning w-100 mt-1" id="constModalSubmitBtn"><i class="fas fa-paper-plane me-1"></i>Submit Inquiry</button>';
    featuresHtml += '<div id="constModalFormResponse" class="alert p-2 mt-2 small d-none"></div>';
    featuresHtml += '</form></div></div></div>';

    body.innerHTML = featuresHtml;
    showBootstrapModal('constModal');

    // Dynamic event binding for modal form
    var modalForm = document.getElementById('constModalInquiryForm');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var mSubmitBtn = document.getElementById('constModalSubmitBtn');
            var mResponse = document.getElementById('constModalFormResponse');
            
            mSubmitBtn.disabled = true;
            mSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            
            var formData = new FormData(modalForm);
            fetch('<?php echo BASE_URL; ?>/service-interest', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(d => {
                mResponse.classList.remove('d-none', 'alert-success', 'alert-danger');
                mResponse.classList.add(d.success ? 'alert-success' : 'alert-danger');
                mResponse.textContent = d.message;
                if (d.success) {
                    modalForm.reset();
                    if (window.APS && window.APS.showNotification) {
                        window.APS.showNotification('Construction inquiry submitted successfully!', 'success');
                    }
                }
                mSubmitBtn.disabled = false;
                mSubmitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Inquiry';
            })
            .catch(err => {
                mResponse.classList.remove('d-none', 'alert-success');
                mResponse.classList.add('alert-danger');
                mResponse.textContent = 'Something went wrong. Please try again.';
                mSubmitBtn.disabled = false;
                mSubmitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Inquiry';
            });
        });
    }
}

function showBootstrapModal(id) {
    if (typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(document.getElementById(id)).show();
    } else {
        setTimeout(function() { showBootstrapModal(id); }, 200);
    }
}

// AJAX form submission for main form
document.getElementById('constructionInquiryForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', '<?= __('const_inquiry_submitted') ?>');
            form.reset();
        } else {
            showToast('error', result.message || '<?= __('const_enquiry_failed') ?>');
        }
    } catch (error) {
        showToast('error', '<?= __('const_enquiry_error') ?>');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>
