<?php
$current_page = $current_page ?? 'book-plot';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse">Browse Plots</a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail">Plot <?= htmlspecialchars($plot['plot_number']) ?></a></li>
            <li class="breadcrumb-item active">Book</li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-contract me-2"></i>Book a Plot</h2>

    <form method="POST" action="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book" id="bookingForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="row g-4">

            <!-- Left: Booking Form -->
            <div class="col-lg-7">

                <!-- Customer Info -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-user me-2"></i>Your Information</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? $user['mobile'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">User ID</label>
                                <input type="text" class="form-control" value="#<?= (int)($user['id'] ?? 0) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Plan -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-credit-card me-2"></i>Payment Plan</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planFull" value="full">
                                    <label class="form-check-label w-100" for="planFull">
                                        <strong class="d-block">Full Payment</strong>
                                        <small class="text-muted">Pay the entire amount upfront. No EMI.</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planEmi" value="emi" checked>
                                    <label class="form-check-label w-100" for="planEmi">
                                        <strong class="d-block">EMI Plan (12 months)</strong>
                                        <small class="text-muted">Pay 25% token + 12 monthly installments @ 10% p.a.</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded p-3 mt-3">
                            <div class="row text-center">
                                <div class="col">
                                    <small class="text-muted d-block">Token Amount</small>
                                    <strong class="text-primary">₹<?= number_format($tokenAmount) ?></strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Balance</small>
                                    <strong>₹<?= number_format($plot['total_price'] - $tokenAmount) ?></strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Total Price</small>
                                    <strong class="text-primary fs-5">₹<?= number_format($plot['total_price']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-sticky-note me-2"></i>Additional Notes</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <textarea class="form-control" name="notes" rows="3" placeholder="Any special requests or questions (optional)"></textarea>
                    </div>
                </div>

                <!-- Terms & Legally Binding Consent -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-gavel me-2 text-primary"></i>Terms & Consent (Legally Binding)</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <!-- Plot Lock Timer -->
                        <div id="lockTimer" class="alert alert-warning mb-3" style="display:none;">
                            <i class="fas fa-clock me-2"></i>
                            <strong>This plot is reserved for you for <span id="lockCountdown">30:00</span></strong>
                            <br><small>If you do not complete booking within this time, the plot will be released.</small>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <a href="<?= $baseUrl ?>/legal/terms-conditions" target="_blank">Terms &amp; Conditions</a>
                                and understand that the 25% token amount (₹<?= number_format($tokenAmount) ?>) is non-refundable once the booking is confirmed.
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="cancellationCheck" required>
                            <label class="form-check-label" for="cancellationCheck">
                                I understand the <a href="<?= $baseUrl ?>/legal/terms-conditions#cancellation" target="_blank">Cancellation Policy</a>:
                                ≤15 days from token: 10% deduction; >15 days: 100% forfeiture. After agreement: 10% of total plot cost.
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="emiTermsCheck" required>
                            <label class="form-check-label" for="emiTermsCheck">
                                I understand that EMI overdue installments attract 18% p.a. penalty (0.0493%/day) after a 5-day grace period.
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="kycCheck" required>
                            <label class="form-check-label" for="kycCheck">
                                I confirm my KYC is completed or I will complete it before registration. I agree to provide PAN card, Aadhaar, and address proof as required by RERA.
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="esignConsent" required>
                            <label class="form-check-label" for="esignConsent">
                                I consent to e-signing the Sale Agreement via Leegality (digital signature platform) and understand the e-signed document has the same legal validity as a physical signature.
                            </label>
                        </div>

                        <div id="kycStatus" class="d-none">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" id="kycBadge" style="background:#f0fdf4;">
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="text-success fw-semibold">KYC Verified</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBooking">
                    <i class="fas fa-check-circle me-2"></i>Confirm Booking Request
                </button>
                <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-1"></i>Back to Plot Detail
                </a>
            </div>

            <!-- Right: Plot Summary -->
            <div class="col-lg-5">
                <div class="aps-cp-card mb-4" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border: none;">
                    <div class="aps-cp-card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name']) ?>
                        </h5>
                        <h3 class="fw-bold mb-3">Plot <?= htmlspecialchars($plot['plot_number']) ?></h3>
                        <div class="fs-2 fw-bold mb-3">₹<?= number_format($plot['total_price']) ?></div>
                        <hr style="border-color: rgba(255,255,255,0.3);">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="opacity-75">Area</small><br>
                                <strong><?= number_format($plot['area_sqft']) ?> sqft</strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Dimensions</small><br>
                                <strong><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Block</small><br>
                                <strong><?= htmlspecialchars($plot['block'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Location</small><br>
                                <strong><?= htmlspecialchars($plot['district_name'] ?? '') ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($plot['corner_plot'])): ?>
                            <span class="badge bg-warning"><i class="fas fa-star me-1"></i>Corner Plot</span>
                        <?php endif; ?>
                        <?php if (!empty($plot['park_facing'])): ?>
                            <span class="badge bg-success"><i class="fas fa-tree me-1"></i>Park Facing</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-info-circle me-2"></i>Payment Terms</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                25% token (₹<?= number_format($tokenAmount) ?>) to confirm
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Balance via EMI or full payment
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Estimated stamp duty: ~₹<?= number_format(round($plot['total_price'] * 0.05)) ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Registration at Sub-Registrar office
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
    const CSRF = '<?= htmlspecialchars($csrfToken) ?>';
    let lockInterval = null;
    let lockExpiresAt = null;

    // ── Plot Lock on page load ──
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

    // ── Release lock on page unload ──
    window.addEventListener('beforeunload', () => {
        navigator.sendBeacon(BASE + '/plots/' + PLOT_ID + '/unlock', new URLSearchParams({
            csrf_token: CSRF
        }));
    });

    // ── KYC verification on form submit ──
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
