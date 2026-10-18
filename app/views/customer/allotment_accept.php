<?php
/** @var array $allotment */
/** @var array|null $acceptance */
$allotment = $allotment ?? [];
$acceptance = $acceptance ?? null;
$base = BASE_URL ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Accept Allotment Letter') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .allotment-card { border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; }
        .otp-input { letter-spacing: 0.5em; font-size: 1.5rem; text-align: center; }
        .document-preview { max-height: 60vh; overflow-y: auto; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-contract me-2"></i>Accept Allotment Letter</h2>
            <p class="text-muted mb-0">Verify and accept your allotment letter via OTP</p>
        </div>
        <a href="<?= $base ?>/customer/documents" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Documents
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-file-contract me-2"></i>Allotment Letter Preview</span>
                </div>
                <div class="aps-cp-card-body document-preview">
                    <div class="text-center mb-3">
                        <h4 class="fw-bold mb-1">APS Dream Home</h4>
                        <p class="text-muted mb-2">Allotment Letter</p>
                        <span class="badge bg-primary"><?= htmlspecialchars($allotment['allotment_number'] ?? 'N/A') ?></span>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Plot</small>
                            <strong><?= htmlspecialchars($allotment['plot_number'] ?? '—') ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Colony</small>
                            <strong><?= htmlspecialchars($allotment['colony_name'] ?? '—') ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Area</small>
                            <strong><?= number_format($allotment['area_sqft'] ?? 0, 2) ?> sq ft</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong class="text-success">₹<?= number_format($allotment['total_amount'] ?? 0, 2) ?></strong>
                        </div>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fas fa-calendar me-1"></i>
                        Generated: <?= date('d M Y', strtotime($allotment['created_at'] ?? 'now')) ?>
                    </div>
                    <?php if (!empty($allotment['file_path'])): ?>
                    <a href="<?= BASE_URL . '/' . $allotment['file_path'] ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-eye me-1"></i>View Full Document
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-shield-halved me-2"></i>Acceptance Status</span>
                </div>
                <div class="aps-cp-card-body text-center py-4">
                    <?php if ($acceptance && $acceptance['status'] === 'accepted'): ?>
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                        <h5 class="text-success">Accepted</h5>
                        <p class="text-muted small">Accepted on <?= date('d M Y H:i', strtotime($acceptance['accepted_at'])) ?></p>
                        <a href="<?= $base ?>/customer/documents" class="btn btn-secondary mt-2">Back to Documents</a>
                    <?php else: ?>
                        <div class="text-warning mb-3">
                            <i class="fas fa-clock fa-3x"></i>
                        </div>
                        <h5 class="text-warning">Pending Acceptance</h5>
                        <p class="text-muted small">Verify OTP to accept this allotment letter</p>
                        <hr>
                        <form method="POST" action="<?= BASE_URL ?>/user/allotment/<?= (int)$allotment['id'] ?>/send-otp" id="sendOtpForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-1"></i> Send OTP
                            </button>
                        </form>
                        <div class="mt-2 small text-muted">
                            OTP will be sent to your registered mobile and email
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$acceptance || $acceptance['status'] !== 'accepted'): ?>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-key me-2"></i>Enter OTP</span>
                </div>
                <div class="aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/user/allotment/<?= (int)$allotment['id'] ?>/accept" id="acceptForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Enter 6-digit OTP</label>
                            <input type="text" name="otp_code" class="form-control otp-input" maxlength="6" required autofocus pattern="\d{6}" placeholder="000000">
                            <div class="form-text">Enter the 6-digit code sent to your mobile/email</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Accept Allotment
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    document.getElementById('sendOtpForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
        try {
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="csrf_token"]').value
                },
                body: new URLSearchParams(new FormData(this))
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
            } else {
                alert(data.error || 'Failed to send OTP');
            }
        } catch (e) {
            alert('Error sending OTP');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send OTP';
        }
    });
</script>
</body>
</html>