<?php

// TODO: Add proper error handling with try-catch blocks

// app/views/pages/careers.php
// Data passed from PageController::careers()
?>
<!-- Hero Section -->
<section class="hero-section text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= get_asset_url('assets/images/hero-3.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4"><?= __('careers_hero_title', null, 'Join Our Team') ?></h1>
                <p class="lead mb-4">
                    <?= __('careers_hero_desc', null, "Build your career with India's most trusted real estate brand. We're looking for passionate individuals ready to make an impact in the property sector.") ?>
                </p>
                <a href="#jobs" class="btn btn-light btn-lg">
                    <i class="fas fa-search me-2"></i><?= __('careers_view_positions', null, 'View Open Positions') ?>
                </a>
            </div>
        </div>
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
                    <li class="breadcrumb-item active" aria-current="page"><?= __('careers_breadcrumb', null, 'Careers') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>


<!-- Why Join Us -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('careers_why_title', null, 'Why Join APS Dream Homes?') ?></h2>
            <p class="lead text-muted"><?= __('careers_why_subtitle', null, 'Discover what makes us a great place to work') ?></p>
        </div>

        <div class="row g-4">
            <?php if (!empty($benefits)): ?>
            <?php foreach ($benefits as $benefit): ?>
            <div class="col-lg-4">
                <div class="value-card">
                    <div class="benefit-icon">
                        <i class="fas <?= htmlspecialchars($benefit['icon'] ?? 'fa-star') ?>"></i>
                    </div>
                    <h5><?= htmlspecialchars($benefit['title']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($benefit['description'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-info-circle me-2"></i><?= __('careers_benefits_soon', null, 'Benefits information coming soon.') ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Current Openings -->
<section class="py-5" id="jobs">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('careers_current_openings') ?></h2>
            <p class="lead text-muted"><?= __('careers_join_growing') ?></p>
        </div>

        <div class="row">
            <?php if (isset($careers) && count($careers) > 0): ?>
                <?php foreach ($careers as $career): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($career->title); ?></h5>
                                    <p class="text-primary mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($career->location ?? 'Gorakhpur, UP'); ?>
                                    </p>
                                </div>
                                <span class="badge bg-success"><?php echo htmlspecialchars($career->type ?? 'Full Time'); ?></span>
                            </div>

                            <?php if (isset($career->salary_min) && isset($career->salary_max)): ?>
                                <div class="mb-2">
                                    <span class="text-primary fw-bold">₹<?php echo number_format($career->salary_min); ?> - ₹<?php echo number_format($career->salary_max); ?></span>
                                    <small class="text-muted"> <?= __('careers_per_month') ?></small>
                                </div>
                            <?php endif; ?>

                            <p class="text-muted mb-3"><?php echo htmlspecialchars(substr($career->description ?? '', 0, 150)) . '...'; ?></p>

                            <!-- Tags/Badges if available -->
                            <div class="mb-3">
                                <?php if (isset($career->department)): ?>
                                    <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($career->department); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <?php if (isset($career->experience)): ?>
                                        <?= __('careers_experience_label') ?>: <?php echo htmlspecialchars($career->experience); ?>
                                    <?php endif; ?>
                                </small>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyModal" onclick="setJobTitle('<?php echo addslashes($career->title); ?>')">
                                    <?= __('careers_apply_now') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4><?= __('careers_no_openings') ?></h4>
                        <p><?= __('careers_no_openings_desc') ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Application Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= sprintf(__('careers_apply_for'), '<span id="jobTitle">Position</span>') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobApplicationForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= __('careers_full_name') ?></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= __('careers_email') ?></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= __('careers_phone') ?></label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= __('careers_position_applied') ?></label>
                            <input type="text" class="form-control" name="position" id="applicationPosition" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('careers_cover_letter') ?></label>
                        <textarea class="form-control" name="cover_letter" rows="4" placeholder="<?= __('careers_cover_letter_ph') ?>"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('careers_resume_cv') ?></label>
                        <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted"><?= __('careers_resume_help') ?></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('careers_cancel') ?></button>
                <button type="button" class="btn btn-primary" onclick="submitApplication()"><?= __('careers_submit_application') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4"><?= __('careers_no_position_title') ?></h2>
                <p class="lead mb-4">
                    <?= __('careers_no_position_desc') ?>
                </p>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-primary btn-lg">
                    <i class="fas fa-envelope me-2"></i><?= __('careers_get_in_touch') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    function setJobTitle(title) {
        document.getElementById('jobTitle').textContent = title;
        document.getElementById('applicationPosition').value = title;
    }

    function submitApplication() {
        const form = document.getElementById('jobApplicationForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);

        // Here you would typically submit to your backend
        // TODO: Implement actual form submission endpoint
        alert('<?= __('careers_submission_success') ?>');

        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('applyModal'));
        modal.hide();
        form.reset();
    }
</script>
