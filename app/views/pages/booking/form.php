<?php
$current_page = $current_page ?? 'book-plot';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse"><?= __('book_browse_plots') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail"><?= __('plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?></a></li>
            <li class="breadcrumb-item active"><?= __('book_book') ?></li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-contract me-2"></i><?= __('book_title') ?></h2>

    <form method="POST" action="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book" id="bookingForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div class="row g-4">

            <!-- Left: Booking Form -->
            <div class="col-lg-7">

                <!-- Customer Info -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-user me-2"></i><?= __('book_your_info') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold"><?= __('book_full_name') ?></label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold"><?= __('book_email') ?></label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold"><?= __('book_phone') ?></label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? $user['mobile'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold"><?= __('book_user_id') ?></label>
                                <input type="text" class="form-control" value="#<?= (int)($user['id'] ?? 0) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associate / Referral Details (Optional) -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header bg-light">
                        <span><i class="fas fa-handshake me-2 text-primary"></i><?= __('book_associate_referral') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold"><?= __('book_referral_code') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                    <input type="text" class="form-control" name="referral_code" id="referralCodeInput"
                                           placeholder="<?= __('book_referral_placeholder') ?>"
                                           value="<?= htmlspecialchars($_SESSION['referral_code'] ?? $_COOKIE['aps_referral'] ?? '') ?>">
                                    <button class="btn btn-outline-primary" type="button" id="btnVerifyReferral"><?= __('book_verify') ?></button>
                                </div>
                                <div class="form-text text-muted small"><?= __('book_referral_help') ?></div>
                            </div>
                            <div class="col-md-6">
                                <div id="referralBadge" class="p-2 rounded border bg-light d-none">
                                    <small class="text-muted d-block"><?= __('book_associate_name') ?></small>
                                    <strong id="associateNameText" class="text-success">—</strong>
                                    <span id="associateRankBadge" class="badge bg-primary ms-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Plan -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-credit-card me-2"></i><?= __('book_payment_plan') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planFull" value="full">
                                    <label class="form-check-label w-100" for="planFull">
                                        <strong class="d-block"><?= __('book_full_payment') ?></strong>
                                        <small class="text-muted"><?= __('book_full_payment_desc') ?></small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planEmi" value="emi" checked>
                                    <label class="form-check-label w-100" for="planEmi">
                                        <strong class="d-block"><?= __('book_emi_plan') ?></strong>
                                        <small class="text-muted"><?= __('book_emi_plan_desc') ?></small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded p-3 mt-3">
                            <div class="row text-center">
                                <div class="col">
                                    <small class="text-muted d-block"><?= __('book_token_amount') ?></small>
                                    <strong class="text-primary">₹<?= number_format($tokenAmount) ?></strong>
                                    <div><span class="badge bg-danger mt-1" style="font-size:0.7rem;">Non-Refundable (वापस नहीं होगी)</span></div>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block"><?= __('book_balance') ?></small>
                                    <strong>₹<?= number_format($plot['total_price'] - $tokenAmount) ?></strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block"><?= __('book_total_price') ?></small>
                                    <strong class="text-primary fs-5">₹<?= number_format($plot['total_price']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-sticky-note me-2"></i><?= __('book_notes') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <textarea class="form-control" name="notes" rows="3" placeholder="<?= __('book_notes_placeholder') ?>"></textarea>
                    </div>
                </div>

                <!-- Terms & Legally Binding Consent -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-gavel me-2 text-primary"></i><?= __('book_terms_consent') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <!-- Plot Lock Timer -->
                        <div id="lockTimer" class="alert alert-warning mb-3">
                            <i class="fas fa-clock me-2"></i>
                            <strong><?= __('book_plot_reserved') ?> <span id="lockCountdown">30:00</span></strong>
                            <br><small><?= __('book_plot_reserved_desc') ?></small>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                <?= __('book_terms_agree') ?>
                                <a href="<?= $baseUrl ?>/legal/terms-conditions" target="_blank"><?= __('book_terms_link') ?></a>
                                <?= __('book_terms_understand') ?>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="cancellationCheck" required>
                            <label class="form-check-label" for="cancellationCheck">
                                <?= __('book_cancellation_understand') ?>
                                <a href="<?= $baseUrl ?>/legal/terms-conditions#cancellation" target="_blank"><?= __('book_cancellation_link') ?></a>:
                                <?= __('book_cancellation_policy') ?>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="emiTermsCheck" required>
                            <label class="form-check-label" for="emiTermsCheck">
                                <?= __('book_emi_penalty_notice') ?>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="kycCheck" required>
                            <label class="form-check-label" for="kycCheck">
                                <?= __('book_kyc_notice') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="esignConsent" required>
                            <label class="form-check-label" for="esignConsent">
                                <?= __('book_esign_notice') ?>
                            </label>
                        </div>

                        <!-- ═══ LEGAL COMPLIANCE: Tripartite Master Deed Clickwrap ═══ -->
                        <div class="border border-danger rounded-3 p-3 mb-3" id="tripartiteConsentBox" style="background: #fff5f5;">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="tripartiteConsent" name="tripartite_consent" required
                                       style="border-color: #dc3545; width: 1.3em; height: 1.3em; margin-top: 0.15em;">
                                <label class="form-check-label fw-semibold" for="tripartiteConsent" style="font-size: 0.92rem; line-height: 1.5; color: #1a1a2e;">
                                    <i class="fas fa-gavel text-danger me-1"></i>
                                    I have read, understood, and unconditionally agree to the
                                    <a href="<?= $baseUrl ?>/terms-conditions" target="_blank" class="fw-bold text-decoration-underline">Master Agreement Terms</a>,
                                    the <a href="<?= $baseUrl ?>/cancellation-policy" target="_blank" class="fw-bold text-decoration-underline">Cancellation Policy</a> (टोकन बुकिंग राशि पूर्णतः गैर-वापसी योग्य है / 100% strictly non-refundable token, 25%/10%/5% admin slabs),
                                    the <a href="<?= $baseUrl ?>/refund-policy" target="_blank" class="fw-bold text-decoration-underline">Refund Policy</a> (180-working-day staggered payout, zero cash),
                                    and the <strong>18% penal interest on 3-month EMI default</strong>.
                                </label>
                                <div class="invalid-feedback">You must accept the cancellation, refund, and master agreement terms to proceed.</div>
                            </div>
                            </div>
                            <div id="tripartiteError" class="text-danger small mt-1 d-none">
                                <i class="fas fa-exclamation-circle me-1"></i>You must accept the Tripartite Master Deed terms to proceed with booking.
                            </div>
                        </div>

                        <div id="kycStatus" class="d-none">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" id="kycBadge" >
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="text-success fw-semibold"><?= __('book_kyc_verified') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBooking">
                    <i class="fas fa-check-circle me-2"></i><?= __('book_confirm_button') ?>
                </button>
                <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-1"></i><?= __('book_back_to_detail') ?>
                </a>
            </div>

            <!-- Right: Plot Summary -->
            <div class="col-lg-5">
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name'] ?? '') ?>
                        </h5>
                        <h3 class="fw-bold mb-3"><?= __('plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h3>
                        <div class="fs-2 fw-bold mb-3">₹<?= number_format($plot['total_price']) ?></div>
                        <hr >
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="opacity-75"><?= __('book_area') ?></small><br>
                                <strong><?= number_format($plot['area_sqft']) ?> sqft</strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75"><?= __('book_dimensions') ?></small><br>
                                <strong><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75"><?= __('book_block') ?></small><br>
                                <strong><?= htmlspecialchars($plot['block'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75"><?= __('book_location') ?></small><br>
                                <strong><?= htmlspecialchars($plot['district_name'] ?? '') ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($plot['corner_plot'])): ?>
                            <span class="badge bg-warning"><i class="fas fa-star me-1"></i><?= __('book_corner_plot') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($plot['park_facing'])): ?>
                            <span class="badge bg-success"><i class="fas fa-tree me-1"></i><?= __('book_park_facing') ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-info-circle me-2"></i><?= __('book_payment_terms') ?></span>
                    </div>
                    <div class="aps-cp-card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?= __('book_token_to_confirm') ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?= __('book_balance_via_emi') ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?= __('book_stamp_duty') ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?= __('book_registration_notice') ?>
                            </li>
                        </ul>
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
    const CSRF = '<?= htmlspecialchars($csrfToken ?? '') ?>';
    let lockInterval = null;
    let lockExpiresAt = null;

    // ═══ Referral Code Verification ═══
    const referralInput = document.getElementById('referralCodeInput');
    const verifyBtn = document.getElementById('btnVerifyReferral');
    const referralBadge = document.getElementById('referralBadge');
    const associateNameText = document.getElementById('associateNameText');
    const associateRankBadge = document.getElementById('associateRankBadge');

    if (verifyBtn && referralInput) {
        verifyBtn.addEventListener('click', function() {
            const code = referralInput.value.trim().toUpperCase();
            if (!code) {
                alert('<?= addslashes(__('book_referral_empty')) ?>');
                return;
            }
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span><?= addslashes(__('book_verifying')) ?>';
            fetch(BASE + '/api/v2/verify-referral?code=' + encodeURIComponent(code), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                verifyBtn.disabled = false;
                verifyBtn.textContent = '<?= addslashes(__('book_verify')) ?>';
                if (data.success && data.associate) {
                    associateNameText.textContent = data.associate.name;
                    associateRankBadge.textContent = data.associate.rank;
                    referralBadge.classList.remove('d-none');
                    referralInput.value = code;
                } else {
                    alert(data.message || '<?= addslashes(__('book_referral_invalid')) ?>');
                    referralBadge.classList.add('d-none');
                }
            })
            .catch(() => {
                verifyBtn.disabled = false;
                verifyBtn.textContent = '<?= addslashes(__('book_verify')) ?>';
                alert('<?= addslashes(__('book_referral_error')) ?>');
            });
        });
    }

    // —€—€ Plot Lock on page load —€—€
    fetch(BASE + '/plots/' + PLOT_ID + '/lock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'csrf_token=' + encodeURIComponent(CSRF)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.expires_at) {
            lockExpiresAt = new Date(data.expires_at.replace(' ', 'T') + 'Z');
            document.getElementById('lockTimer').style.display = '';
            startCountdown();
        }
    })
    .catch(() => {});

    function startCountdown() {
        lockInterval = setInterval(() => {
            const now = new Date();
            const diff = Math.max(0, Math.floor((lockExpiresAt - now) / 1000));
            if (diff <= 0) {
                clearInterval(lockInterval);
                document.getElementById('lockCountdown').textContent = '00:00';
                document.getElementById('lockTimer').className = 'alert alert-danger mb-3';
                document.getElementById('lockTimer').innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Plot reservation expired.</strong> Please refresh the page to check availability.';
                document.getElementById('submitBooking').disabled = true;
                return;
            }
            const mins = Math.floor(diff / 60);
            const secs = diff % 60;
            document.getElementById('lockCountdown').textContent =
                String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }, 1000);
    }

    // —€—€ Release lock on page unload —€—€
    window.addEventListener('beforeunload', () => {
        navigator.sendBeacon(BASE + '/plots/' + PLOT_ID + '/unlock', new URLSearchParams({
            csrf_token: CSRF
        }));
    });

    // —€—€ KYC verification on form submit —€—€
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        // Check all checkboxes
        const checks = ['termsCheck', 'cancellationCheck', 'emiTermsCheck', 'kycCheck', 'esignConsent'];
        for (const id of checks) {
            if (!document.getElementById(id).checked) {
                e.preventDefault();
                alert('Please agree to all terms before proceeding.');
                return false;
            }
        }

        // ═══ LEGAL COMPLIANCE: Tripartite consent gate ═══
        const tripartiteEl = document.getElementById('tripartiteConsent');
        const tripartiteErr = document.getElementById('tripartiteError');
        if (!tripartiteEl.checked) {
            e.preventDefault();
            tripartiteErr.classList.remove('d-none');
            tripartiteEl.closest('.form-check').classList.add('shake');
            tripartiteEl.focus();
            return false;
        }
        tripartiteErr.classList.add('d-none');

        // AJAX KYC check
        e.preventDefault();
        const form = this;
        fetch(BASE + '/plots/' + PLOT_ID + '/verify-kyc', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'csrf_token=' + encodeURIComponent(CSRF)
        })
        .then(r => r.json())
        .then(data => {
            if (data.verified) {
                form.submit();
            } else {
                if (confirm('KYC is not yet verified. You can still book, but registration may be delayed.\n\n' + (data.message || 'Continue with booking?'))) {
                    form.submit();
                }
            }
        })
        .catch(() => {
            form.submit(); // KYC check failed, allow submission
        });
    });
})();
</script>
