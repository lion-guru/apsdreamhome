<?php
$page_title = $page_title ?? 'System Setup & Onboarding Wizard - APS Dream Home';
$company = $company ?? [];
$stats = $stats ?? ['users' => 0, 'colonies' => 0, 'plots' => 0, 'leads' => 0];
$colonies = $colonies ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Setup & Onboarding Wizard</li>
        </ol>
    </nav>

    <!-- Wizard Component Container -->
    <div class="aps-cp-wizard shadow-sm border-0" data-aps-wizard id="onboardingWizard">
        <!-- Header -->
        <div class="aps-cp-wizard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="aps-cp-wizard-title mb-1">
                        <i class="fas fa-magic me-2"></i>Real Estate ERP Onboarding Wizard
                    </h4>
                    <p class="aps-cp-wizard-subtitle mb-0 opacity-75">
                        Set up your agency foundation in 5 guided steps: Company, Team, Colonies, Plots, and Leads.
                    </p>
                </div>
                <div>
                    <span class="badge bg-white text-primary px-3 py-2 fw-bold rounded-pill">
                        <i class="fas fa-check-circle me-1 text-success"></i>System Setup v2.0
                    </span>
                </div>
            </div>
        </div>

        <!-- Step Navigation Bar -->
        <ul class="aps-cp-wizard-steps">
            <li class="aps-cp-wizard-step active" data-step="0">
                <div class="aps-cp-wizard-step-num"><span>1</span></div>
                <div class="aps-cp-wizard-step-label">
                    <span class="fw-bold d-block">Company</span>
                    <small class="text-muted">Profile & RERA</small>
                </div>
            </li>
            <li class="aps-cp-wizard-step" data-step="1">
                <div class="aps-cp-wizard-step-num"><span>2</span></div>
                <div class="aps-cp-wizard-step-label">
                    <span class="fw-bold d-block">Team</span>
                    <small class="text-muted">Users & Roles</small>
                </div>
            </li>
            <li class="aps-cp-wizard-step" data-step="2">
                <div class="aps-cp-wizard-step-num"><span>3</span></div>
                <div class="aps-cp-wizard-step-label">
                    <span class="fw-bold d-block">Colonies</span>
                    <small class="text-muted">Projects</small>
                </div>
            </li>
            <li class="aps-cp-wizard-step" data-step="3">
                <div class="aps-cp-wizard-step-num"><span>4</span></div>
                <div class="aps-cp-wizard-step-label">
                    <span class="fw-bold d-block">Inventory</span>
                    <small class="text-muted">Plots Batch</small>
                </div>
            </li>
            <li class="aps-cp-wizard-step" data-step="4">
                <div class="aps-cp-wizard-step-num"><span>5</span></div>
                <div class="aps-cp-wizard-step-label">
                    <span class="fw-bold d-block">Leads & Launch</span>
                    <small class="text-muted">Review & Go</small>
                </div>
            </li>
        </ul>

        <!-- Wizard Panels Body -->
        <div class="aps-cp-wizard-body p-4">
            
            <!-- PANEL 1: Company Profile -->
            <div class="aps-cp-wizard-panel active" id="panel-step-1">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-building me-2"></i>Step 1: Agency & Company Details</h5>
                        <p class="text-muted small mb-0">Enter your real estate enterprise details. These appear on invoices, receipts, and client passes.</p>
                    </div>
                </div>

                <form id="form-step-1">
                    <input type="hidden" name="step" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($company['company_name'] ?? 'APS Dream Home Corp') ?>" required placeholder="e.g. APS Dream Home Real Estate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($company['tagline'] ?? 'Your Trusted Real Estate Partner') ?>" placeholder="e.g. Building Dreams, Delivering Trust">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($company['phone'] ?? '+91 9876543210') ?>" placeholder="+91 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Contact Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($company['email'] ?? 'contact@apsdreamhome.com') ?>" placeholder="info@yourcompany.com">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Head Office Address</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($company['address'] ?? 'Gorakhpur, Uttar Pradesh') ?>" placeholder="Full street address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($company['city'] ?? 'Gorakhpur') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">RERA Registration No.</label>
                            <input type="text" name="rera_number" class="form-control" value="<?= htmlspecialchars($company['rera_number'] ?? 'UPRERAAGT2026') ?>" placeholder="e.g. UPRERAAGT12345">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($company['gstin'] ?? '09AAACA0000A1Z5') ?>" placeholder="GST Identification Number">
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveStepData(1)">
                            <i class="fas fa-save me-1"></i> Save Company Profile & Proceed
                        </button>
                    </div>
                </form>
            </div>

            <!-- PANEL 2: Users & Roles -->
            <div class="aps-cp-wizard-panel" id="panel-step-2">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-users me-2"></i>Step 2: Team Members & Roles Setup</h5>
                        <p class="text-muted small mb-0">Create your initial team members (Sales Executive, Telecaller, Associate). You have <?= $stats['users'] ?> existing users.</p>
                    </div>
                </div>

                <form id="form-step-2">
                    <input type="hidden" name="step" value="2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" required placeholder="e.g. Amit Kumar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="user_email" class="form-control" required placeholder="e.g. amit.sales@apsdreamhome.test">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="user_phone" class="form-control" placeholder="10-digit mobile">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">System Role <span class="text-danger">*</span></label>
                            <select name="user_role" class="form-select">
                                <option value="employee" selected>Employee (Sales / Operations)</option>
                                <option value="associate">Associate / Partner (MLM Network)</option>
                                <option value="agent">Agent (Freelance Broker)</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Initial Password</label>
                            <input type="text" name="user_password" class="form-control" value="Aps@2026">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="wizardShow(0)">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="wizardShow(2)">
                                Skip to Colonies <i class="fas fa-forward ms-1"></i>
                            </button>
                            <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveStepData(2)">
                                <i class="fas fa-user-plus me-1"></i> Add Member & Next
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PANEL 3: Colonies & Projects -->
            <div class="aps-cp-wizard-panel" id="panel-step-3">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-map-marked-alt me-2"></i>Step 3: Colony / Township Project</h5>
                        <p class="text-muted small mb-0">Add a residential or commercial township colony. You have <?= $stats['colonies'] ?> active project(s).</p>
                    </div>
                </div>

                <form id="form-step-3">
                    <input type="hidden" name="step" value="3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Colony Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="colony_name" class="form-control" required placeholder="e.g. Royal City Phase-1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / Landmark <span class="text-danger">*</span></label>
                            <input type="text" name="colony_location" class="form-control" required placeholder="e.g. Medical College Road, Gorakhpur">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="colony_city" class="form-control" value="Gorakhpur">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Total Planned Plots</label>
                            <input type="number" name="total_plots" class="form-control" value="60" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Starting Price (₹)</label>
                            <input type="number" name="starting_price" class="form-control" value="750000" step="10000">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="wizardShow(1)">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="wizardShow(3)">
                                Skip to Inventory <i class="fas fa-forward ms-1"></i>
                            </button>
                            <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveStepData(3)">
                                <i class="fas fa-plus-circle me-1"></i> Create Project & Next
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PANEL 4: Plot Inventory Batch Generator -->
            <div class="aps-cp-wizard-panel" id="panel-step-4">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-th me-2"></i>Step 4: Batch Plot Inventory Generation</h5>
                        <p class="text-muted small mb-0">Generate inventory in seconds by defining a number series (e.g. P-101 to P-120). Currently <?= $stats['plots'] ?> total plots in database.</p>
                    </div>
                </div>

                <form id="form-step-4">
                    <input type="hidden" name="step" value="4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target Colony Project <span class="text-danger">*</span></label>
                            <select name="plot_colony_id" id="plot_colony_id" class="form-select" required>
                                <?php if (!empty($colonies)): ?>
                                    <?php foreach ($colonies as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['location']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Default Colony Project</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plot Type</label>
                            <select name="plot_type" class="form-select">
                                <option value="residential" selected>Residential Plot</option>
                                <option value="commercial">Commercial Plot</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Series Prefix</label>
                            <input type="text" name="plot_prefix" class="form-control" value="P-" placeholder="e.g. P- or B-">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Start Number</label>
                            <input type="number" name="start_number" class="form-control" value="101" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">End Number</label>
                            <input type="number" name="end_number" class="form-control" value="120" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Area (Sq. Ft)</label>
                            <input type="number" name="plot_area_sqft" class="form-control" value="1000" min="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Base Rate per Sq. Ft (₹)</label>
                            <input type="number" name="plot_rate_sqft" class="form-control" value="1250" min="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estimated Plot Price (Auto)</label>
                            <input type="text" class="form-control bg-light" id="estimated_price" readonly value="₹ 12,50,000">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="wizardShow(2)">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="wizardShow(4)">
                                Skip to Review <i class="fas fa-forward ms-1"></i>
                            </button>
                            <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveStepData(4)">
                                <i class="fas fa-layer-group me-1"></i> Generate Plots & Next
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PANEL 5: Leads & Launch Review -->
            <div class="aps-cp-wizard-panel" id="panel-step-5">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-check-double me-2"></i>Step 5: Leads Ingestion & System Review</h5>
                        <p class="text-muted small mb-0">Capture your first prospect lead and review system launch readiness.</p>
                    </div>
                </div>

                <!-- Initial Lead Capture Form -->
                <div class="card border mb-4 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user-tag me-2 text-primary"></i>Capture Initial Prospect Lead</h6>
                        <form id="form-step-5">
                            <input type="hidden" name="step" value="5">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Prospect Name <span class="text-danger">*</span></label>
                                    <input type="text" name="lead_name" class="form-control form-control-sm" value="Vikas Verma" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Mobile Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="lead_phone" class="form-control form-control-sm" value="9876543211" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Lead Source</label>
                                    <select name="lead_source" class="form-select form-select-sm">
                                        <option value="Website Inquiry" selected>Website Inquiry</option>
                                        <option value="Facebook Ad">Facebook Ad</option>
                                        <option value="Walk-in">Walk-in Visitor</option>
                                        <option value="Referral">Associate Referral</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Estimated Budget (₹)</label>
                                    <input type="number" name="lead_budget" class="form-control form-control-sm" value="1500000">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Notes / Requirements</label>
                                    <input type="text" name="lead_notes" class="form-control form-control-sm" value="Looking for 1000 sq.ft corner plot near medical college">
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" onclick="saveStepData(5)">
                                    <i class="fas fa-save me-1"></i> Save Prospect Lead
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Live System Readiness Overview Cards -->
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Live Readiness Overview</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="aps-cp-stat aps-cp-stat--blue">
                            <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" id="stat-users"><?= $stats['users'] ?></div>
                                <div class="aps-cp-stat-label">System Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="aps-cp-stat aps-cp-stat--green">
                            <div class="aps-cp-stat-icon"><i class="fas fa-map-marked-alt"></i></div>
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" id="stat-colonies"><?= $stats['colonies'] ?></div>
                                <div class="aps-cp-stat-label">Township Projects</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="aps-cp-stat aps-cp-stat--orange">
                            <div class="aps-cp-stat-icon"><i class="fas fa-th"></i></div>
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" id="stat-plots"><?= $stats['plots'] ?></div>
                                <div class="aps-cp-stat-label">Total Plots</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="aps-cp-stat aps-cp-stat--purple">
                            <div class="aps-cp-stat-icon"><i class="fas fa-funnel-dollar"></i></div>
                            <div class="aps-cp-stat-body">
                                <div class="aps-cp-stat-value" id="stat-leads"><?= $stats['leads'] ?></div>
                                <div class="aps-cp-stat-label">Captured Leads</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Launch Action -->
                <div class="p-4 border rounded-3 bg-white text-center">
                    <div class="mb-3">
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i>Core Configuration Ready
                        </span>
                    </div>
                    <h4 class="fw-bold mb-2">Ready to Launch Your Operations Cockpit!</h4>
                    <p class="text-muted mb-4">All modules are calibrated. You can now manage plots, assign leads, record payments, and monitor commissions.</p>
                    <button type="button" class="btn btn-success btn-lg px-5 fw-bold shadow" onclick="completeOnboarding()">
                        <i class="fas fa-rocket me-2"></i> Complete Setup & Launch Cockpit
                    </button>
                </div>
            </div>

        </div>

        <!-- Wizard Footer Progress -->
        <div class="aps-cp-wizard-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrev" onclick="stepNav(-1)">
                <i class="fas fa-chevron-left me-1"></i> Previous
            </button>
            <div class="aps-cp-wizard-progress">
                <div class="aps-cp-wizard-progress-bar" id="wizardProgressBar" style="width: 20%;"></div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" id="btnNext" onclick="stepNav(1)">
                Next <i class="fas fa-chevron-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<script>
let currentStepIdx = 0;
const totalSteps = 5;

function wizardShow(stepIdx) {
    if (stepIdx < 0 || stepIdx >= totalSteps) return;
    currentStepIdx = stepIdx;

    // Toggle panels
    document.querySelectorAll('.aps-cp-wizard-panel').forEach((p, i) => {
        p.classList.toggle('active', i === stepIdx);
    });

    // Toggle step navigation headers
    document.querySelectorAll('.aps-cp-wizard-step').forEach((s, i) => {
        s.classList.remove('active', 'completed');
        if (i < stepIdx) s.classList.add('completed');
        else if (i === stepIdx) s.classList.add('active');
    });

    // Progress bar
    const pct = Math.round(((stepIdx + 1) / totalSteps) * 100);
    const pb = document.getElementById('wizardProgressBar');
    if (pb) pb.style.width = pct + '%';

    // Buttons visibility
    const prevBtn = document.getElementById('btnPrev');
    const nextBtn = document.getElementById('btnNext');
    if (prevBtn) prevBtn.style.visibility = stepIdx === 0 ? 'hidden' : 'visible';
    if (nextBtn) nextBtn.style.visibility = stepIdx === totalSteps - 1 ? 'hidden' : 'visible';

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function stepNav(delta) {
    wizardShow(currentStepIdx + delta);
}

function saveStepData(stepNum) {
    const form = document.getElementById('form-step-' + stepNum);
    if (!form) return;

    const fd = new FormData(form);

    fetch('<?= $base ?>/admin/onboarding/save-step', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (typeof APS !== 'undefined' && APS.toast) {
                APS.toast(res.message, 'success');
            } else {
                alert(res.message);
            }

            // Update colonies dropdown if step 3 was saved
            if (stepNum === 3 && res.colony) {
                const sel = document.getElementById('plot_colony_id');
                if (sel) {
                    const opt = document.createElement('option');
                    opt.value = res.colony.id;
                    opt.textContent = res.colony.name;
                    opt.selected = true;
                    sel.appendChild(opt);
                }
            }

            // Advance to next step
            if (stepNum < totalSteps) {
                wizardShow(stepNum);
            }
        } else {
            alert(res.message || 'Error saving step.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while saving step.');
    });
}

function completeOnboarding() {
    fetch('<?= $base ?>/admin/onboarding/complete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.redirect) {
            window.location.href = res.redirect;
        } else {
            alert(res.message || 'Onboarding finished.');
            window.location.href = '<?= $base ?>/admin/dashboard';
        }
    })
    .catch(err => {
        window.location.href = '<?= $base ?>/admin/dashboard';
    });
}

// Interactive calculation of estimated plot price
document.querySelectorAll('input[name="plot_area_sqft"], input[name="plot_rate_sqft"]').forEach(inp => {
    inp.addEventListener('input', function() {
        const area = parseFloat(document.querySelector('input[name="plot_area_sqft"]').value) || 0;
        const rate = parseFloat(document.querySelector('input[name="plot_rate_sqft"]').value) || 0;
        const total = area * rate;
        const el = document.getElementById('estimated_price');
        if (el) el.value = '₹ ' + total.toLocaleString('en-IN');
    });
});
</script>
