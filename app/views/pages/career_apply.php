<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<?php
/**
 * APS Dream Home - Career Application Page
 */

$page_title = "Career Application - APS Dream Home";
$description = "Apply for exciting career opportunities at APS Dream Home. Join our team of real estate professionals.";
?>

<!-- Hero Section -->
<section class="careers-hero bg-dark text-white py-5 mb-0 position-relative overflow-hidden">
    <div class="container py-5 mt-4 text-center" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3"><?= __('career_apply_hero_title', null, 'Career Application') ?></h1>
        <p class="lead opacity-75 mx-auto"><?= __('career_apply_hero_subtitle', null, 'Take the first step towards a rewarding career in real estate') ?></p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav_home', null, 'Home') ?></a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>careers"><?= __('careers_breadcrumb', null, 'Careers') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= __('career_apply_breadcrumb', null, 'Apply') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark"><?= __('career_apply_cta_title', null, "Ready to Join Our Team?") ?></h2>
                            <p class="text-muted"><?= __('career_apply_cta_desc', null, "Fill out the form below and we'll get back to you soon.") ?></p>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['error'] ?? '') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['success'] ?? '') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>careers/apply" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold"><?= __('testi_lbl_name', null, 'Full Name') ?> *</label>
                                    <input type="text" class="form-control" id="name" name="full_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold"><?= __('career_apply_email_lbl', null, 'Email Address') ?> *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold"><?= __('career_apply_phone_lbl', null, 'Phone Number') ?></label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label fw-semibold"><?= __('career_apply_position_lbl', null, 'Position Applied For') ?> *</label>
                                    <select class="form-select" id="position" name="career_id" required>
                                        <option value="">Select Position</option>
                                        <?php if (!empty($careers)): ?>
                                        <?php foreach ($careers as $job): ?>
                                        <option value="<?= $job->id ?>"><?= htmlspecialchars($job->title) ?></option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="experience" class="form-label fw-semibold"><?= __('career_apply_experience_lbl', null, 'Years of Experience') ?></label>
                                    <input type="number" class="form-control" id="experience" name="experience_years" min="0" max="50">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="current_company" class="form-label fw-semibold">Current Company</label>
                                    <input type="text" class="form-control" id="current_company" name="current_company">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label fw-semibold"><?= __('career_apply_cover_lbl', null, 'Cover Letter / Message') ?></label>
                                <textarea class="form-control" id="message" name="cover_letter" rows="5" placeholder="Tell us why you're interested in this position..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="resume" class="form-label fw-semibold"><?= __('career_apply_resume_lbl', null, 'Resume/CV') ?></label>
                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <div class="form-text"><?= __('career_apply_resume_hint', null, 'Accepted formats: PDF, DOC, DOCX (Max size: 5MB)') ?></div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                                    <i class="fas fa-paper-plane me-2"></i><?= __('career_apply_btn_submit', null, 'Submit Application') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><?= __('career_apply_other_ways', null, 'Other Ways to Apply') ?></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-envelope text-primary me-3"></i>
                                    <div>
                                        <strong><?= __('career_apply_email_lbl') ?>:</strong><br>
                                        <a href="mailto:hr@apsdreamhome.com">hr@apsdreamhome.com</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-phone text-primary me-3"></i>
                                    <div>
                                        <strong><?= __('career_apply_phone_lbl') ?>:</strong><br>
                                        <a href="tel:+919876543210"><?= htmlspecialchars($phoneDisplay) ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map-marker-alt text-primary me-3"></i>
                            <div>
                                <strong><?= __('career_apply_office_lbl', null, 'Office') ?>:</strong><br>
                                <?= __('career_apply_office_addr', null, 'APS Dream Home, Kaushambi, Ghaziabad, Uttar Pradesh') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .careers-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding-bottom: 100px !important;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
</style>
