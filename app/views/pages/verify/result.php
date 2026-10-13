<?php
$page_title = $page_title ?? 'Document Verification';
$result = $result ?? ['success' => false, 'verified' => false, 'message' => 'No verification data'];
$type = $type ?? '';
$id = $id ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="Verify document authenticity via QR code">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/aps-components.css?v=2" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .verification-card { border-radius: 16px; overflow: hidden; }
        .verified-badge { background: linear-gradient(135deg, #10b981, #059669); }
        .not-verified-badge { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .pending-badge { background: linear-gradient(135deg, #f59e0b, #d97706); }
    </style>
</head>
<body class="bg-light min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card verification-card shadow-lg border-0">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea, #764ba2);">
                                <i class="fas fa-qrcode fa-2x text-white"></i>
                            </div>
                            <h2 class="fw-bold mb-1">Document Verification</h2>
                            <p class="text-muted mb-0">Scan result for document verification</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($result['success'])): ?>
                            <?php if ($result['verified']): ?>
                                <div class="text-center mb-4">
                                    <div class="badge verified-badge text-white fs-6 px-4 py-3 d-inline-flex align-items-center gap-2">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                        <span>VERIFIED</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center mb-4">
                                    <div class="badge not-verified-badge text-white fs-6 px-4 py-3 d-inline-flex align-items-center gap-2">
                                        <i class="fas fa-times-circle fa-lg"></i>
                                        <span>NOT VERIFIED</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="alert alert-<?= $result['verified'] ? 'success' : 'danger' ?> border-0 shadow-sm">
                                <i class="fas fa-<?= $result['verified'] ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                                <?= htmlspecialchars($result['message'] ?? '') ?>
                            </div>
                            
                            <?php if (!empty($result['document'])): ?>
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="fw-bold mb-0"><i class="fas fa-file-alt me-2"></i>Document Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 small">
                                            <div class="col-6">
                                                <label class="text-muted">Document Type</label>
                                                <div class="fw-semibold"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $type ?? ''))) ?></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-muted">Document ID</label>
                                                <div class="fw-semibold">#<?= $id ?></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-muted">Status</label>
                                                <div class="fw-semibold">
                                                    <span class="badge bg-<?= in_array(strtolower($result['document']['status'] ?? ''), ['verified', 'approved', 'active']) ? 'success' : (($result['document']['status'] ?? '') === 'pending' ? 'warning' : 'secondary') ?>">
                                                        <?= htmlspecialchars(ucfirst($result['document']['status'] ?? 'Unknown')) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-muted">Verification Time</label>
                                                <div class="fw-semibold"><?= date('d M Y H:i') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 mt-4">
                                <a href="<?= BASE_URL ?>/" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                                <a href="<?= BASE_URL ?>/verify/document/<?= $type ?>/<?= $id ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync me-1"></i>Re-verify
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
                                <h4 class="fw-bold mb-2">Verification Failed</h4>
                                <p class="text-muted mb-4"><?= htmlspecialchars($result['message'] ?? 'Unable to verify document') ?></p>
                                <a href="<?= BASE_URL ?>/" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Auto-reload for demo
        if (window.location.search.includes('auto')) {
            setTimeout(() => location.reload(), 5000);
        }
    </script>
</body>
</html>