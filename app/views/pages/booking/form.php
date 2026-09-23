<?php
$current_page = $current_page ?? 'book-plot';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$totalPrice = floatval($plot['total_price'] ?? 0);
$tokenAmount = 51000;
$twentyFivePercent = $totalPrice * 0.25;
$balanceDue15Days = max(0, $twentyFivePercent - $tokenAmount);
$remainingSeventyFive = max(0, $totalPrice - $twentyFivePercent);
$simulatedEmi36 = $remainingSeventyFive / 36;
?>

<div class="container py-4">

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>" class="text-decoration-none"><?= __('home', 'Home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse" class="text-decoration-none"><?= __('book_browse_plots', 'Browse Plots') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/<?= (int)$plot['id'] ?>/detail" class="text-decoration-none"><?= __('plot', 'Plot') ?> <?= htmlspecialchars((string)($plot['plot_number'] ?? '')) ?></a></li>
            <li class="breadcrumb-item active"><?= __('book_book', 'Booking Application') ?></li>
        </ol>
    </nav>

    <!-- Page Header & Statutory Subtitle -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="fas fa-file-contract text-primary me-2"></i><?= __('book_title', 'Plot Booking Application') ?>
            </h2>
            <p class="text-muted small mb-0">Official Tripartite Master Legal Deed (V8) Compliant Allotment Engine</p>
        </div>
        <div class="mt-2 mt-md-0">
            <span class="badge bg-danger fs-6 px-3 py-2 shadow-xs">
                <i class="fas fa-shield-alt me-1"></i>₹51,000 Non-Refundable Token Standard
            </span>
        </div>
    </div>

    <!-- 5-Stage Systematic Stepper -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <div class="row text-center g-2">
                <div class="col">
                    <div class="p-2 rounded bg-white border border-success">
                        <span class="badge bg-success rounded-pill mb-1">Step 1</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-check-circle me-1 text-success"></i>Plot Selected</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border border-primary shadow-xs">
                        <span class="badge bg-primary rounded-pill mb-1">Step 2</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-user-check me-1 text-primary"></i>Buyer Profile</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border border-primary shadow-xs">
                        <span class="badge bg-primary rounded-pill mb-1">Step 3</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-calendar-alt me-1 text-primary"></i>Payment Plan</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border border-danger">
                        <span class="badge bg-danger rounded-pill mb-1">Step 4</span>
                        <div class="fw-bold small text-danger"><i class="fas fa-gavel me-1 text-danger"></i>Master Consent</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border text-muted">
                        <span class="badge bg-secondary rounded-pill mb-1">Step 5</span>
                        <div class="fw-bold small"><i class="fas fa-receipt me-1"></i>Token Payment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= $baseUrl ?>/plots/<?= (int)$plot['id'] ?>/book" id="bookingForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken) ?>">

        <div class="row g-4">
            <!-- Left Column: Form Steps -->
            <div class="col-lg-7">

                <!-- 1. Customer Info Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-id-card text-primary me-2"></i><?= __('book_your_info', 'Applicant (Allottee) Particulars') ?></h6>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold">Verified Account</span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1"><?= __('book_full_name', 'Full Legal Name') ?></label>
                                <input type="text" class="form-control bg-light fw-bold" value="<?= htmlspecialchars((string)($user['name'] ?? '')) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1"><?= __('book_email', 'Email Address') ?></label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars((string)($user['email'] ?? '')) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1"><?= __('book_phone', 'Registered Mobile') ?></label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars((string)($user['phone'] ?? $user['mobile'] ?? '')) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1"><?= __('book_user_id', 'Customer Account ID') ?></label>
                                <input type="text" class="form-control bg-light" value="#<?= (int)($user['id'] ?? 0) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Associate / Referral Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-handshake text-primary me-2"></i><?= __('book_associate_referral', 'Associate / Channel Partner Attribution (Optional)') ?>
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold text-muted mb-1"><?= __('book_referral_code', 'Enter Associate Referral Code') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-qrcode"></i></span>
                                    <input type="text" class="form-control text-uppercase fw-bold" name="referral_code" id="referralCodeInput"
                                           placeholder="e.g. APS-1024"
                                           value="<?= htmlspecialchars((string)($_SESSION['referral_code'] ?? $_COOKIE['aps_referral'] ?? '')) ?>">
                                    <button class="btn btn-outline-primary" type="button" id="btnVerifyReferral"><?= __('book_verify', 'Verify') ?></button>
                                </div>
                                <small class="text-muted"><?= __('book_referral_help', 'Attributing your booking helps your local property advisor assist with documentation.') ?></small>
                            </div>
                            <div class="col-md-5">
                                <div id="referralBadge" class="p-2 rounded border bg-light d-none">
                                    <small class="text-muted d-block"><?= __('book_associate_name', 'Verified Associate:') ?></small>
                                    <strong id="associateNameText" class="text-success">—</strong>
                                    <span id="associateRankBadge" class="badge bg-primary ms-1"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Payment Plan & EMI Selection -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-credit-card text-primary me-2"></i><?= __('book_payment_plan', 'Payment Plan & Consideration Schedule') ?></h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planFull" value="full">
                                    <label class="form-check-label w-100" for="planFull">
                                        <strong class="d-block text-dark"><?= __('book_full_payment', 'Lump Sum Full Payment') ?></strong>
                                        <small class="text-muted"><?= __('book_full_payment_desc', '100% upfront settlement with priority registry & immediate demarcation.') ?></small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border border-primary rounded-3 p-3 h-100 bg-primary bg-opacity-10">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planEmi" value="emi" checked>
                                    <label class="form-check-label w-100" for="planEmi">
                                        <strong class="d-block text-primary"><?= __('book_emi_plan', '36-Month Easy EMI Plan') ?></strong>
                                        <small class="text-muted"><?= __('book_emi_plan_desc', 'Master Deed Section 4 Schedule: 25% Down Payment + 36 Monthly Installments.') ?></small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Ledger Breakdown Box -->
                        <div class="rounded-3 p-3 mt-4 border border-info" style="background-color: #f0f9ff;">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-calculator text-info me-2"></i>Commercial Breakdown for Plot #<?= htmlspecialchars((string)($plot['plot_number'] ?? '')) ?></h6>
                            <div class="row g-3 text-center">
                                <div class="col-md-4">
                                    <div class="p-2 bg-white rounded border border-danger">
                                        <small class="text-danger fw-bold d-block">Stage 1: Token Due Now</small>
                                        <strong class="fs-5 text-danger">₹51,000</strong>
                                        <span class="badge bg-danger d-block mt-1">100% Non-Refundable</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 bg-white rounded border border-warning">
                                        <small class="text-dark fw-bold d-block">Stage 2: 25% Bal. (15 Days)</small>
                                        <strong class="fs-5 text-dark">₹<?= number_format((float)$balanceDue15Days) ?></strong>
                                        <small class="text-muted d-block mt-1">25% Total Less Token</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 bg-white rounded border border-primary">
                                        <small class="text-primary fw-bold d-block">Stage 3: 36 EMIs (75%)</small>
                                        <strong class="fs-5 text-primary">₹<?= number_format((float)$simulatedEmi36, 0) ?>/mo</strong>
                                        <small class="text-muted d-block mt-1">Total: ₹<?= number_format((float)$remainingSeventyFive) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Notes & Remarks -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sticky-note text-primary me-2"></i><?= __('book_notes', 'Special Remarks & Preferences') ?></h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <textarea class="form-control" name="notes" rows="2" placeholder="<?= __('book_notes_placeholder', 'Enter any preferred possession timeline, registry co-allottee details, or instructions...') ?>"></textarea>
                    </div>
                </div>

                <!-- 5. Master Deed Statutory Consent (Cleanly Nested) -->
                <div class="card shadow-sm border-0 border-top border-3 border-danger mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-balance-scale text-danger me-2"></i>Tripartite Master Legal Deed (V8) Consent</h6>
                        <span class="badge bg-danger">Mandatory Statutory Gate</span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <!-- Plot Lock Timer -->
                        <div id="lockTimer" class="alert alert-warning mb-3">
                            <i class="fas fa-clock me-2"></i>
                            <strong><?= __('book_plot_reserved', 'Plot Temporary Reservation:') ?> <span id="lockCountdown" class="badge bg-dark ms-1">30:00</span></strong>
                            <br><small><?= __('book_plot_reserved_desc', 'This plot is temporarily locked for you. Complete token checkout before the timer expires.') ?></small>
                        </div>

                        <!-- Checkbox 1: Terms -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                            <label class="form-check-label small" for="termsCheck">
                                I accept the <a href="<?= $baseUrl ?>/legal/terms-conditions" target="_blank" class="fw-bold">General Terms &amp; Conditions</a> governing plot allotments.
                            </label>
                        </div>

                        <!-- Checkbox 2: 15-Day 25% Condition -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="cancellationCheck" required checked>
                            <label class="form-check-label small" for="cancellationCheck">
                                I understand that <strong>25% of the total plot consideration must be deposited within 15 calendar days</strong> from today, failing which the booking stands cancelled.
                            </label>
                        </div>

                        <!-- Checkbox 3: Default Interest -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="emiTermsCheck" required checked>
                            <label class="form-check-label small" for="emiTermsCheck">
                                I accept that delayed installments beyond 3 consecutive months attract <strong>18% per annum penal interest</strong> and ₹1,000 bounce fee per NACH/cheque default.
                            </label>
                        </div>

                        <!-- Checkbox 4: Tripartite Master Deed Clickwrap Box -->
                        <div class="p-3 rounded-3 border border-danger mb-3 mt-3" id="tripartiteConsentBox" style="background: #fff8f8;">
                            <div class="form-check mb-0">
                                <input class="form-check-input border-danger" type="checkbox" id="tripartiteConsent" name="tripartite_consent" required checked
                                       style="width: 1.25em; height: 1.25em; margin-top: 0.15em;">
                                <label class="form-check-label fw-bold text-danger ms-1" for="tripartiteConsent" style="font-size: 0.9rem; line-height: 1.5;">
                                    <i class="fas fa-gavel text-danger me-1"></i>
                                    Master Deed Section 2.1 &amp; 2.9 Statutory Acknowledgment:
                                </label>
                                <p class="small text-dark mb-0 mt-1 ps-4" style="line-height: 1.5;">
                                    I confirm and accept that the <strong>initial booking token consideration of ₹51,000 is 100% strictly Non-Refundable / गैर-वापसी योग्य</strong> under all circumstances to cover plot blockage and administrative costs. Refunds upon permitted cancellation follow the 180-working-day staggered cycle under Sections 2.1 and 2.9.
                                </p>
                            </div>
                            <div id="tripartiteError" class="text-danger small mt-2 d-none">
                                <i class="fas fa-exclamation-circle me-1"></i>You must accept the Tripartite Master Deed terms to proceed with booking.
                            </div>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="esignConsent" required checked>
                            <label class="form-check-label small" for="esignConsent">
                                I consent to electronic record execution and legal dispatch via registered Email and SMS/WhatsApp.
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Submit Action Controls -->
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow mb-2" id="submitBooking">
                        <i class="fas fa-check-circle me-2"></i>Proceed to ₹51,000 Token Payment
                    </button>
                    <a href="<?= $baseUrl ?>/plots/<?= (int)$plot['id'] ?>/detail" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i><?= __('book_back_to_detail', 'Back to Plot Specifications') ?>
                    </a>
                </div>
            </div>

            <!-- Right Column: Sticky Summary & Demarcation -->
            <div class="col-lg-5">
                <div class="position-sticky" style="top: 20px;">
                    <!-- Plot Details Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-building text-warning me-2"></i>Plot Specification Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark"><?= __('plot', 'Plot') ?> #<?= htmlspecialchars((string)($plot['plot_number'] ?? '')) ?></h4>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars((string)($plot['colony_name'] ?? '')) ?> &middot; <?= htmlspecialchars((string)($plot['district_name'] ?? 'Gorakhpur')) ?></small>
                                </div>
                                <span class="badge bg-success">Available</span>
                            </div>

                            <div class="fs-2 fw-bold text-primary mb-3">₹<?= number_format((float)$totalPrice) ?></div>
                            <hr class="my-2">

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block"><?= __('book_area', 'Carpet / Super Area') ?></small>
                                    <strong><?= number_format((float)($plot['area_sqft'] ?? 0)) ?> sq.ft.</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block"><?= __('book_dimensions', 'Dimensions') ?></small>
                                    <strong><?= htmlspecialchars((string)($plot['dimension_label'] ?? '—')) ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block"><?= __('book_block', 'Sector / Block') ?></small>
                                    <strong><?= htmlspecialchars((string)($plot['block'] ?? '—')) ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Facing &amp; Road</small>
                                    <strong><?= htmlspecialchars((string)($plot['facing'] ?? 'East')) ?> &middot; 30ft Road</strong>
                                </div>
                            </div>

                            <!-- 4 Concrete Pillars Demarcation Badge -->
                            <div class="p-3 bg-light border border-info rounded-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-monument fa-2x text-info me-3"></i>
                                    <div>
                                        <strong class="d-block text-dark" style="font-size: 0.88rem;">4 Corner Concrete Pillars</strong>
                                        <small class="text-muted">Physically demarcated &amp; surveyed boundaries as per Master Deed Section 2.15.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Highlights -->
                            <?php if (!empty($plot['corner_plot']) || !empty($plot['park_facing'])): ?>
                            <div class="d-flex gap-2">
                                <?php if (!empty($plot['corner_plot'])): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Corner Plot (PLC Applicable)</span>
                                <?php endif; ?>
                                <?php if (!empty($plot['park_facing'])): ?>
                                    <span class="badge bg-success"><i class="fas fa-tree me-1"></i>Park Facing</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Corporate Assistance Help Card -->
                    <div class="card shadow-sm border-0 bg-white">
                        <div class="card-body p-3 text-center">
                            <i class="fas fa-headset text-primary fa-2x mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">Need Booking Support?</h6>
                            <p class="small text-muted mb-2">Our legal &amp; sales desk in Gorakhpur is available to guide your documentation.</p>
                            <a href="tel:+919277121112" class="btn btn-sm btn-outline-primary"><i class="fas fa-phone-alt me-1"></i>+91 92771 21112</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const BASE = '<?= $baseUrl ?>';
    const PLOT_ID = <?= (int)$plot['id'] ?>;
    const CSRF = '<?= htmlspecialchars((string)$csrfToken) ?>';
    let lockInterval = null;
    let lockExpiresAt = null;

    // Referral verification
    const referralInput = document.getElementById('referralCodeInput');
    const verifyBtn = document.getElementById('btnVerifyReferral');
    const referralBadge = document.getElementById('referralBadge');
    const associateNameText = document.getElementById('associateNameText');
    const associateRankBadge = document.getElementById('associateRankBadge');

    if (verifyBtn && referralInput) {
        verifyBtn.addEventListener('click', function() {
            const code = referralInput.value.trim().toUpperCase();
            if (!code) {
                alert('Please enter an associate code to verify.');
                return;
            }
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';
            fetch(BASE + '/api/v2/verify-referral?code=' + encodeURIComponent(code), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify';
                if (data.success && data.associate) {
                    associateNameText.textContent = data.associate.name;
                    associateRankBadge.textContent = data.associate.rank || 'Associate';
                    referralBadge.classList.remove('d-none');
                    referralInput.value = code;
                } else {
                    alert(data.message || 'Invalid associate referral code.');
                    referralBadge.classList.add('d-none');
                }
            })
            .catch(() => {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify';
                alert('Failed to verify associate code. Please try again.');
            });
        });
    }

    // Plot Lock Countdown
    fetch(BASE + '/plots/' + PLOT_ID + '/lock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'csrf_token=' + encodeURIComponent(CSRF)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.expires_at) {
            lockExpiresAt = new Date(data.expires_at.replace(' ', 'T') + 'Z');
            startCountdown();
        }
    })
    .catch(() => {});

    function startCountdown() {
        if (!lockExpiresAt) return;
        lockInterval = setInterval(() => {
            const now = new Date();
            const diff = Math.max(0, Math.floor((lockExpiresAt - now) / 1000));
            if (diff <= 0) {
                clearInterval(lockInterval);
                document.getElementById('lockCountdown').textContent = '00:00';
                document.getElementById('lockTimer').className = 'alert alert-danger mb-3';
                document.getElementById('lockTimer').innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Plot reservation expired.</strong> Please refresh the page to renew.';
                document.getElementById('submitBooking').disabled = true;
                return;
            }
            const mins = Math.floor(diff / 60);
            const secs = diff % 60;
            document.getElementById('lockCountdown').textContent =
                String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }, 1000);
    }

    window.addEventListener('beforeunload', () => {
        navigator.sendBeacon(BASE + '/plots/' + PLOT_ID + '/unlock', new URLSearchParams({
            csrf_token: CSRF
        }));
    });

    // Form submit validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const checks = ['termsCheck', 'cancellationCheck', 'emiTermsCheck', 'tripartiteConsent', 'esignConsent'];
        for (const id of checks) {
            const el = document.getElementById(id);
            if (el && !el.checked) {
                e.preventDefault();
                alert('Please accept all statutory terms and conditions to proceed.');
                el.focus();
                return false;
            }
        }
    });
})();
</script>
