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
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/user/bookings"><?= __('esign_my_bookings') ?></a></li>
            <li class="breadcrumb-item active"><?= __('esign_title') ?></li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-signature me-2"></i><?= __('esign_title') ?></h2>

    <div class="row g-4">

        <!-- Left: Booking + Agreement Details -->
        <div class="col-lg-8">

            <!-- Booking Summary -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i><?= __('esign_booking_summary') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('esign_booking_number') ?></small>
                            <strong><?= htmlspecialchars($booking['booking_number'] ?? 'â€”') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('esign_plot') ?></small>
                            <strong><?= htmlspecialchars($booking['plot_number'] ?? '') ?> â€” <?= htmlspecialchars($booking['colony_name'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('esign_area') ?></small>
                            <strong><?= number_format($booking['area_sqft'] ?? 0) ?> <?= __('sqft') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('esign_dimensions') ?></small>
                            <strong><?= htmlspecialchars($booking['dimension_label'] ?? 'â€”') ?></strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block"><?= __('esign_total_amount') ?></small>
                            <strong class="fs-5 text-primary">â‚¹<?= number_format($booking['total_plot_value'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-Sign Status Card -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-file-signature me-2"></i><?= __('esign_signing_status') ?></span>
                    <span class="badge bg-<?= $esignStatus === 'signed' ? 'success' : ($esignStatus === 'failed' ? 'danger' : ($esignStatus === 'sent' ? 'warning' : 'secondary')) ?>">
                        <?= ucfirst($esignStatus) ?>
                    </span>
                </div>
                <div class="aps-cp-card-body">

                    <?php if ($esignStatus === 'pending'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5><?= __('esign_agreement_ready') ?></h5>
                            <p class="text-muted"><?= __('esign_agreement_ready_desc') ?></p>
                            <button id="btn-initiate-esign" class="btn btn-primary btn-lg mt-2" onclick="initiateEsign()">
                                <i class="fas fa-pen-fancy me-2"></i><?= __('esign_sign_now') ?>
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'sent'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-paper-plane fa-3x text-warning mb-3"></i>
                            <h5><?= __('esign_request_sent') ?></h5>
                            <p class="text-muted"><?= __('esign_request_sent_desc') ?></p>
                            <?php if (!empty($esign['signing_url'])): ?>
                                <a href="<?= htmlspecialchars($esign['signing_url']) ?>" target="_blank" class="btn btn-outline-primary mt-2">
                                    <i class="fas fa-external-link-alt me-1"></i><?= __('esign_open_signing_link') ?>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary btn-sm mt-2 ms-2" onclick="checkStatus()">
                                <i class="fas fa-sync-alt me-1"></i><?= __('esign_refresh_status') ?>
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'signed'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5><?= __('esign_signed_success') ?></h5>
                            <p class="text-muted"><?= sprintf(__('esign_signed_desc'), htmlspecialchars($esign['signed_at'] ?? 'â€”')) ?></p>
                            <button class="btn btn-success mt-2" onclick="verifySignature()">
                                <i class="fas fa-certificate me-1"></i><?= __('esign_verify_signature') ?>
                            </button>
                        </div>

                    <?php elseif ($esignStatus === 'failed'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5><?= __('esign_signing_failed') ?></h5>
                            <p class="text-muted"><?= __('esign_signing_failed_desc') ?></p>
                            <button class="btn btn-primary mt-2" onclick="initiateEsign()">
                                <i class="fas fa-redo me-1"></i><?= __('esign_retry_signing') ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Status Detail (hidden by default, shown via JS) -->
                    <div id="esign-detail" class="mt-3" class="style-2248">
                        <div class="alert alert-info mb-0" id="esign-detail-content"></div>
                    </div>
                </div>
            </div>

            <!-- Agreement Info -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i><?= __('esign_about_esign') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('esign_legal_valid') ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('esign_aadhaar_ekyc') ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('esign_tamper_proof') ?></li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i><?= __('esign_download_pdf') ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right: Quick Actions -->
        <div class="col-lg-4">

            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body d-grid gap-2">
                    <a href="<?= $baseUrl ?>/booking/confirmation/<?= $booking['id'] ?? 0 ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i><?= __('esign_back_to_booking') ?>
                    </a>
                    <a href="<?= $baseUrl ?>/user/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-tachometer-alt me-1"></i><?= __('esign_my_dashboard') ?>
                    </a>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold"><?= __('esign_need_help') ?></h6>
                    <p class="small text-muted mb-2"><?= __('esign_trouble_signing') ?></p>
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('esign_initiating') ?>';
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
            alert(data.error || '<?= __('esign_initiate_error') ?>');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-pen-fancy me-2"></i><?= __('esign_sign_now') ?>';
            }
        }
    })
    .catch(function() {
        alert('<?= __('esign_network_error') ?>');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-pen-fancy me-2"></i><?= __('esign_sign_now') ?>';
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
                showDetail('<?= __('esign_current_status') ?>: <strong>' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</strong>. <?= __('esign_please_wait') ?>');
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
            var msg = '<?= __('esign_verification_result') ?>: ';
            msg += data.verified ? '<span class="text-success fw-bold">VERIFIED</span>' : '<span class="text-warning">PENDING</span>';
            if (data.signed_at) {
                msg += '<br><?= __('esign_signed_at') ?>: ' + data.signed_at;
            }
            showDetail(msg);
        }
    })
    .catch(function() {
        showDetail('<?= __('esign_verification_failed') ?>');
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
