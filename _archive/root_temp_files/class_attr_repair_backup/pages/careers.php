<?php
/**
 * Careers Page — APS Dream Home
 * Data from Career\CareerController@index
 */
?>
<style>
.careers-hero {
    background: linear-gradient(rgba(13,110,253,.85), rgba(11,94,215,.92)),
                url('<?= get_asset_url('assets/images/hero-3.jpg') ?>');
    background-size: cover; background-position: center;
}
.careers-hero .lead { max-width: 600px; margin-inline: auto; opacity: .92; }

.benefit-icon {
    width: 68px; height: 68px; border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.1rem; color: #fff; font-size: 1.5rem;
    box-shadow: 0 6px 20px rgba(13,110,253,.35);
}
.value-card {
    text-align: center; padding: 2rem 1.4rem; border-radius: 14px;
    background: #fff; border: 1px solid #e9ecef; height: 100%;
    transition: transform .22s, box-shadow .22s;
}
.value-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(0,0,0,.09); }
.value-card h5 { font-size: 1.05rem; font-weight: 600; margin-bottom: .5rem; }
.value-card p { font-size: .92rem; line-height: 1.6; }

.pillar-card {
    border-radius: 16px; padding: 2rem 1.5rem; height: 100%;
    background: #fff; border: 1px solid #e9ecef;
    transition: transform .22s, box-shadow .22s, border-color .22s;
}
.pillar-card:hover { transform: translateY(-5px); box-shadow: 0 14px 36px rgba(0,0,0,.08); border-color: #0d6efd; }
.pillar-icon {
    width: 64px; height: 64px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1rem; font-size: 1.5rem; color: #fff;
}

.job-card {
    border: 1px solid #e9ecef; border-radius: 14px; padding: 1.6rem;
    background: #fff; height: 100%; display: flex; flex-direction: column;
    transition: transform .22s, box-shadow .22s, border-color .22s;
}
.job-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,.08); border-color: #0d6efd; }
.job-card h5 { font-size: 1.05rem; font-weight: 600; }

.rank-table th { font-weight: 600; font-size: .82rem; white-space: nowrap; }
.rank-table td { font-size: .85rem; vertical-align: middle; }
.rank-table tbody tr:nth-child(even) { background: rgba(13,110,253,.03); }

.careers-section { padding: 4.5rem 0; }
.careers-cta {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    border-radius: 16px; padding: 3.5rem 2rem; margin-top: 1rem;
}
.careers-cta p { opacity: .92; max-width: 560px; margin-inline: auto; }

@media (max-width: 767.98px) {
    .job-card, .pillar-card { padding: 1.1rem; }
    .value-card { padding: 1.5rem 1rem; }
    .benefit-icon { width: 54px; height: 54px; font-size: 1.25rem; }
    .pillar-icon { width: 50px; height: 50px; font-size: 1.25rem; }
    .careers-cta { padding: 2.5rem 1.2rem; border-radius: 12px; }
    .rank-table { font-size: .78rem; }
}
</style>

<!-- •�•�•�•�•�•�•�•�•�•�•�•� HERO •�•�•�•�•�•�•�•�•�•�•�•� -->
<section class="careers-hero text-white py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-3"><?= __('careers_hero_title', null, 'Build Your Future with APS Dream Home') ?></h1>
                <p class="lead mb-4">
                    <?= __('careers_hero_desc', null, 'Join APS Dream Home — Eastern UP\'s leading real estate company. We\'re building premium colonies across Gorakhpur and Deoria.') ?>
                </p>
                <a href="#jobs" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-search me-2"></i><?= __('careers_view_positions', null, 'View Open Positions') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- •�•�•�•�•�•�•�•�•�•�•�•� BREADCRUMB •�•�•�•�•�•�•�•�•�•�•�•� -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav_home', null, 'Home') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= __('careers_breadcrumb', null, 'Careers') ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- •�•�•�•�•�•�•�•�•�•�•�•� WHY JOIN US •�•�•�•�•�•�•�•�•�•�•�•� -->
<section class="careers-section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge text-bg-primary px-3 py-2 mb-3">
                <i class="fas fa-rocket me-1"></i><?= __('home_career_opportunity', null, 'Career Opportunity') ?>
            </span>
            <h2 class="fw-bold"><?= __('home_why_join_title', null, 'Why Join APS Dream Home?') ?></h2>
            <p class="lead text-muted mt-2"><?= __('home_why_join_subtitle', null, 'A new beginning in Real Estate — with Salary + Commission + Insurance!') ?></p>
            <div class="mx-auto mt-3" class="style-60068"></div>
        </div>

        <!-- Core Pillars -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon" class="style-68656"><i class="fas fa-sack-dollar"></i></div>
                    <h5 class="fw-bold mb-2"><?= __('home_fixed_salary', null, 'Fixed Monthly Salary') ?></h5>
                    <p class="text-muted small mb-3"><?= __('home_fixed_salary_desc', null, 'In real estate usually only commission is given. But APS Dream Home also gives fixed monthly salary based on your sales performance!') ?></p>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0 rank-table">
                            <thead>
                                <tr class="style-20604">
                                    <th class="ps-2 py-2">Rank</th>
                                    <th class="py-2 text-center">Business</th>
                                    <th class="py-2 text-center">Direct Sale %</th>
                                    <th class="pe-2 py-2 text-end">Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="ps-2 fw-semibold">Associate</td><td class="text-center">₹10L+</td><td class="text-center fw-bold" class="style-65172">5%</td><td class="text-end text-muted">Mobile</td></tr>
                                <tr><td class="ps-2 fw-semibold">Sr. Associate</td><td class="text-center">₹35L+</td><td class="text-center fw-bold" class="style-65172">7%</td><td class="text-end text-muted">Tablet</td></tr>
                                <tr><td class="ps-2 fw-semibold">BDM</td><td class="text-center">₹70L+</td><td class="text-center fw-bold" class="style-65172">10%</td><td class="text-end text-muted">Laptop</td></tr>
                                <tr><td class="ps-2 fw-semibold">Sr. BDM</td><td class="text-center">₹1.5Cr+</td><td class="text-center fw-bold" class="style-65172">12%</td><td class="text-end text-muted">Tour Package</td></tr>
                                <tr><td class="ps-2 fw-semibold">Vice President</td><td class="text-center">₹3Cr+</td><td class="text-center fw-bold" class="style-65172">15%</td><td class="text-end text-muted">Bike</td></tr>
                                <tr><td class="ps-2 fw-semibold">President</td><td class="text-center">₹5Cr+</td><td class="text-center fw-bold" class="style-65172">18%</td><td class="text-end text-muted">Royal Enfield</td></tr>
                                <tr><td class="ps-2 fw-semibold">Site Manager</td><td class="text-center">₹5Cr+</td><td class="text-center fw-bold" class="style-65172">20%</td><td class="text-end text-muted">Car</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon" class="style-77533"><i class="fas fa-heartbeat"></i></div>
                    <h5 class="fw-bold mb-2">Free Insurance Cover</h5>
                    <p class="text-muted small">We care for your family's security. All active partners receive free health, life, and accident insurance coverage.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon" class="style-82660"><i class="fas fa-graduation-cap"></i></div>
                    <h5 class="fw-bold mb-2">Training &amp; Certification</h5>
                    <p class="text-muted small">No experience? No problem! Our 7-day induction program and skill workshops turn you into a sales and property expert.</p>
                </div>
            </div>
        </div>

        <!-- MLM Network Benefits — REAL data from mlm_rank_benefits + rank_bonus_amounts -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="pillar-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="pillar-icon me-3" class="style-75102"><i class="fas fa-crown"></i></div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= __('home_mlm_benefits', null, 'MLM Network Benefits') ?></h4>
                            <p class="text-muted small mb-0"><?= __('home_mlm_benefits_desc', null, 'Build your network and earn residual commission on their sales. 7 rank structure — earning increases with every rank!') ?></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0 rank-table">
                            <thead>
                                <tr class="style-20604">
                                    <th class="ps-2 py-2">Rank</th>
                                    <th class="py-2 text-center">GBV Threshold</th>
                                    <th class="py-2 text-center">Commission %</th>
                                    <th class="py-2 text-center">Monthly Bonus</th>
                                    <th class="pe-2 py-2 text-end">Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="ps-2 fw-semibold">Associate</td><td class="text-center">₹10L</td><td class="text-center fw-bold" class="style-65172">5%</td><td class="text-center text-muted">—</td><td class="text-end text-muted">Mobile</td></tr>
                                <tr><td class="ps-2 fw-semibold">Sr. Associate</td><td class="text-center">₹35L</td><td class="text-center fw-bold" class="style-65172">7%</td><td class="text-center text-muted">₹5,000/mo</td><td class="text-end text-muted">Tablet</td></tr>
                                <tr><td class="ps-2 fw-semibold">BDM</td><td class="text-center">₹70L</td><td class="text-center fw-bold" class="style-65172">10%</td><td class="text-center text-muted">₹15,000/mo</td><td class="text-end text-muted">Laptop</td></tr>
                                <tr><td class="ps-2 fw-semibold">Sr. BDM</td><td class="text-center">₹1.5Cr</td><td class="text-center fw-bold" class="style-65172">12%</td><td class="text-center text-muted">₹35,000/mo</td><td class="text-end text-muted">Tour Package</td></tr>
                                <tr><td class="ps-2 fw-semibold">Vice President</td><td class="text-center">₹3Cr</td><td class="text-center fw-bold" class="style-65172">15%</td><td class="text-center text-muted">₹75,000/mo</td><td class="text-end text-muted">Bike</td></tr>
                                <tr><td class="ps-2 fw-semibold">President</td><td class="text-center">₹5Cr</td><td class="text-center fw-bold" class="style-65172">18%</td><td class="text-center text-muted">₹1,50,000/mo</td><td class="text-end text-muted">Royal Enfield</td></tr>
                                <tr><td class="ps-2 fw-semibold">Site Manager</td><td class="text-center">₹5Cr+</td><td class="text-center fw-bold" class="style-65172">20%</td><td class="text-center text-muted">₹3,00,000/mo</td><td class="text-end text-muted">Car</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Benefits from DB -->
        <div class="text-center mb-4">
            <h4 class="fw-bold text-primary">More Benefits</h4>
        </div>
        <div class="row g-4">
            <?php if (!empty($benefits)): ?>
                <?php foreach ($benefits as $benefit): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="value-card">
                            <div class="benefit-icon">
                                <i class="fas <?= htmlspecialchars($benefit['icon'] ?? 'fa-star') ?>"></i>
                            </div>
                            <h5><?= htmlspecialchars($benefit['title'] ?? '') ?></h5>
                            <p class="text-muted mb-0"><?= htmlspecialchars($benefit['description'] ?? '') ?></p>
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

<!-- •�•�•�•�•�•�•�•�•�•�•�•� CURRENT OPENINGS •�•�•�•�•�•�•�•�•�•�•�•� -->
<section class="careers-section" id="jobs">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?= __('careers_current_openings', null, 'Current Openings') ?></h2>
            <p class="lead text-muted mt-2"><?= __('careers_join_growing', null, 'Join our growing team and make a difference') ?></p>
        </div>

        <div class="row">
            <?php if (isset($careers) && count($careers) > 0): ?>
                <?php foreach ($careers as $career): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($career->title) ?></h5>
                                    <p class="text-primary mb-1 small">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($career->location ?? 'Gorakhpur, UP') ?>
                                    </p>
                                </div>
                                <span class="badge bg-success"><?= htmlspecialchars($career->type ?? 'Full Time') ?></span>
                            </div>

                            <?php if (!empty($career->salary_min) && !empty($career->salary_max)): ?>
                                <div class="mb-2">
                                    <span class="text-primary fw-bold">₹<?= number_format($career->salary_min) ?> — ₹<?= number_format($career->salary_max) ?></span>
                                    <small class="text-muted"><?= __('careers_per_annum', null, '/annum') ?></small>
                                </div>
                            <?php endif; ?>

                            <p class="text-muted mb-3 small"><?= htmlspecialchars(mb_substr($career->description ?? '', 0, 140)) ?>—¦</p>

                            <?php if (!empty($career->department)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($career->department) ?></span>
                                    <?php if (!empty($career->experience)): ?>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-clock me-1"></i><?= htmlspecialchars($career->experience) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-3 d-flex justify-content-end">
                                <button class="btn btn-primary btn-sm px-3" type="button"
                                        data-bs-toggle="modal" data-bs-target="#applyModal"
                                        onclick="setJobTitle('<?= addslashes($career->title) ?>', <?= (int)($career->id ?? 0) ?>)">
                                    <i class="fas fa-paper-plane me-1"></i><?= __('careers_apply_now', null, 'Apply Now') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3 d-block"></i>
                    <h4 class="text-muted"><?= __('careers_no_openings', null, 'No current openings') ?></h4>
                    <p class="text-muted"><?= __('careers_no_openings_desc', null, 'We don\'t have specific openings right now, but we\'re always looking for talent. Send your resume!') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- •�•�•�•�•�•�•�•�•�•�•�•� APPLICATION MODAL •�•�•�•�•�•�•�•�•�•�•�•� -->
<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applyModalLabel">
                    <?= sprintf(__('careers_apply_for', null, 'Apply for %s'), '<span id="jobTitle" class="fw-bold">Position</span>') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="jobApplicationForm" novalidate>
                    <input type="hidden" name="career_id" id="applicationCareerId" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><?= __('careers_full_name', null, 'Full Name') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Enter your full name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><?= __('careers_email', null, 'Email') ?> <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><?= __('careers_phone', null, 'Phone') ?> <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" placeholder="9876543210" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><?= __('careers_position_applied', null, 'Position Applied For') ?></label>
                            <input type="text" class="form-control" name="position" id="applicationPosition" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?= __('careers_cover_letter', null, 'Cover Letter') ?></label>
                        <textarea class="form-control" name="cover_letter" rows="3" placeholder="<?= __('careers_cover_letter_ph', null, 'Tell us why you are interested in this position...') ?>"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?= __('careers_resume_cv', null, 'Resume/CV') ?> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted"><?= __('careers_resume_help', null, 'PDF, DOC, or DOCX only — Max 5MB') ?></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('careers_cancel', null, 'Cancel') ?></button>
                <button type="button" class="btn btn-primary px-4" id="careerSubmitBtn">
                    <i class="fas fa-paper-plane me-1"></i><?= __('careers_submit_application', null, 'Submit Application') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- •�•�•�•�•�•�•�•�•�•�•�•� CTA •�•�•�•�•�•�•�•�•�•�•�•� -->
<section class="careers-section">
    <div class="container">
        <div class="careers-cta text-center">
            <h2 class="text-white mb-3"><?= __('careers_no_position_title', null, "Don't See a Position That Fits?") ?></h2>
            <p class="text-white lead mb-4">
                <?= __('careers_no_position_desc', null, "We're always looking for talented individuals. Send us your resume and we'll keep you in mind for future opportunities.") ?>
            </p>
            <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg px-4">
                <i class="fas fa-envelope me-2"></i><?= __('careers_get_in_touch', null, 'Get in Touch') ?>
            </a>
        </div>
    </div>
</section>

<script>
function setJobTitle(title, careerId) {
    document.getElementById('jobTitle').textContent = title;
    document.getElementById('applicationPosition').value = title;
    document.getElementById('applicationCareerId').value = careerId || '';
}

document.getElementById('careerSubmitBtn').addEventListener('click', function() {
    var form = document.getElementById('jobApplicationForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    var formData = new FormData(form);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('csrf_token', csrfToken);
    var btn = this;
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';

    fetch('<?= BASE_URL ?>/careers/submit-application', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('applyModal'));
            if (modal) modal.hide();
            form.reset();
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(function() { showToast('Network error. Please try again.', 'danger'); })
    .finally(function() { btn.disabled = false; btn.innerHTML = orig; });
});
</script>
