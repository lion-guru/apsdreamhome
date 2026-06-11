<?php
$current_page = $current_page ?? 'booking-esign';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$esignStatus = $esign['status'] ?? 'pending';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/user/bookings">My Bookings</a></li>
            <li class="breadcrumb-item active">E-Sign Agreement</li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-signature me-2"></i>E-Sign Agreement</h2>

    <div class="row g-4">

        <!-- Left: Booking + Agreement Details -->
        <div class="col-lg-8">

            <!-- Booking Summary -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i>Booking Summary</span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Booking Number</small>
                            <strong><?= htmlspecialchars($booking['booking_number'] ?? '—') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Plot</small>
                            <strong><?= htmlspecialchars($booking['plot_number'] ?? '') ?> — <?= htmlspecialchars($booking['colony_name'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Area</small>
                            <strong><?= number_format($booking['area_sqft'] ?? 0) ?> sqft</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Dimensions</small>
                            <strong><?= htmlspecialchars($booking['dimension_label'] ?? '—') ?></strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong class="fs-5 text-primary">₹<?= number_format($booking['total_plot_value'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-Sign Status Card -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-file-signature me-2"></i>Signing Status</span>
                    <span class="badge bg-<?= $esignStatus === 'signed' ? 'success' : ($esignStatus === 'failed' ? 'danger' : ($esignStatus === 'sent' ? 'warning' : 'secondary')) ?>">
                        <?= ucfirst($esignStatus) ?>
                    </span>
                </div>
                <div class="aps-cp-card-body">

                    <?php if ($esignStatus === 'pending'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5>Agreement Ready for Signing</h5>
                            <p class="text-muted">Your booking agreement is prepared and ready for e-signature. Click the button below to initiate the signing process.</p>
                            <button id="btn-initiate-esign" class="btn btn-primary btn-lg mt-2" onclick="initiateEsign()">
                                <i class="fas fa-pen-fancy me-2"></i>Sign Agreement Now
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'sent'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-paper-plane fa-3x text-warning mb-3"></i>
                            <h5>Signing Request Sent</h5>
                            <p class="text-muted">A signing link has been sent to your email. Please check your inbox and complete the signing process.</p>
                            <?php if (!empty($esign['signing_url'])): ?>
                                <a href="<?= htmlspecialchars($esign['signing_url']) ?>" target="_blank" class="btn btn-outline-primary mt-2">
                                    <i class="fas fa-external-link-alt me-1"></i>Open Signing Link
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary btn-sm mt-2 ms-2" onclick="checkStatus()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Status
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'signed'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Agreement Signed Successfully</h5>
                            <p class="text-muted">Your agreement has been signed on <strong><?= htmlspecialchars($esign['signed_at'] ?? '—') ?></strong>.</p>
                            <button class="btn btn-success mt-2" onclick="verifySignature()">
                                <i class="fas fa-certificate me-1"></i>Verify Signature
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'failed'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5>Signing Failed</h5>
                            <p class="text-muted">The signing process could not be completed. Please try again or contact support.</p>
                            <button class="btn btn-primary mt-2" onclick="initiateEsign()">
                                <i class="fas fa-redo me-1"></i>Retry Signing
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Status Detail (hidden by default, shown via JS) -->
                    <div id="esign-detail" class="mt-3" style="display:none;">
                        <div class="alert alert-info mb-0" id="esign-detail-content"></div>
                    </div>
                </div>
            </div>

            <!-- Agreement Info -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i>About E-Signing</span>
                </div>
                <div class="aps-cp-card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Legally valid under the IT Act, 2000</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Aadhaar-based eKYC verification</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Secure and tamper-proof signing</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Download signed PDF after completion</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right: Quick Actions -->
        <div class="col-lg-4">

            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body d-grid gap-2">
                    <a href="<?= $baseUrl ?>/booking/confirmation/<?= $booking['id'] ?? 0 ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Booking
                    </a>
                    <a href="<?= $baseUrl ?>/user/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-tachometer-alt me-1"></i>My Dashboard
                    </a>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold">Need Help?</h6>
                    <p class="small text-muted mb-2">Having trouble signing? Contact us.</p>
                    <a href="tel:+919277121112" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-phone me-1"></i>+91 92771 21112
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function initiateEsign() {
    var btn = document.getElementById('btn-initiate-esign');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Initiating...';
    }

    fetch('<?= $baseUrl ?>/user/bookings/<?= $booking['id'] ?? 0 ?>/esign/initiate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=<?= urlencode($csrfToken) ?>'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (data.signing_url) {
                window.location.href = data.signing_url;
            } else {
                window.location.reload();
            }
        } else {
            alert(data.error || 'Failed to initiate e-sign. Please try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-pen-fancy me-2"></i>Sign Agreement Now';
            }
        }
    })
    .catch(function() {
        alert('Network error. Please try again.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-pen-fancy me-2"></i>Sign Agreement Now';
        }
    });
}

function checkStatus() {
    fetch('<?= $baseUrl ?>/user/bookings/<?= $booking['id'] ?? 0 ?>/esign', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.status) {
            if (data.status === 'signed') {
                window.location.reload();
            } else {
                showDetail('Current status: <strong>' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</strong>. Please wait for the signing to complete.');
            }
        }
    })
    .catch(function() {});
}

function verifySignature() {
    fetch('<?= $baseUrl ?>/user/bookings/<?= $booking['id'] ?? 0 ?>/esign', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var msg = 'Verification result: ';
            msg += data.verified ? '<span class="text-success fw-bold">VERIFIED</span>' : '<span class="text-warning">PENDING</span>';
            if (data.signed_at) {
                msg += '<br>Signed at: ' + data.signed_at;
            }
            showDetail(msg);
        }
    })
    .catch(function() {
        showDetail('Verification request failed. Please try again.');
    });
}

function showDetail(html) {
    var el = document.getElementById('esign-detail');
    var content = document.getElementById('esign-detail-content');
    if (el && content) {
        content.innerHTML = html;
        el.style.display = 'block';
    }
}
</script>
