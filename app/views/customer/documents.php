<?php
/**
 * Customer Document Locker View
 * Displays all customer documents: Allotment Letter, Payment Passbook, Agreement to Sell
 */
$page_title = 'My Documents - APS Dream Home';
$base = BASE_URL ?? '';
$user = $user ?? [];
$userDocuments = $userDocuments ?? [];
$bookingDocuments = $bookingDocuments ?? [];
$agreementDocuments = $agreementDocuments ?? [];
$base = BASE_URL ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'My Documents') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .document-card { transition: all 0.3s ease; }
        .document-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .document-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .doc-type-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-allotment { background: #dbeafe; color: #1e40af; }
        .badge-passbook { background: #dcfce7; color: #166534; }
        .badge-agreement { background: #fef3c7; color: #92400e; }
        .document-actions .btn { transition: all 0.2s ease; }
        .document-actions .btn:hover { transform: translateY(-1px); }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="fas fa-folder-open me-2"></i>My Documents</h2>
                        <p class="text-muted mb-0">Manage and download your important documents</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>/customer/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Allotment Letters -->
            <div class="col-12 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-file-contract text-primary me-2"></i> Allotment Letters
                </h5>
                <div class="row g-3">
                    <?php if (empty($allotmentDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Allotment Letters Found</h6>
                                    <p class="text-muted small">Your allotment letters will appear here once a plot is allotted.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($allotmentDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-primary bg-opacity-10 text-primary">
                                                <i class="fas fa-file-contract"></i>
                                            </div>
                                            <span class="doc-type-badge badge-allotment">Allotment Letter</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Allotment Letter') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-building me-1"></i>
                                            <?= htmlspecialchars($doc['colony_name'] ?? 'N/A') ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/customer/documents/download/allotment/<?= $doc['id'] ?>" class="btn btn-sm btn-primary w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Passbooks -->
            <div class="col-12 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-book text-success me-2"></i> Payment Passbooks
                </h5>
                <div class="row g-3">
                    <?php if (empty($passbookDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Passbooks Found</h6>
                                    <p class="text-muted small">Payment passbooks will appear here after payments are made.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($passbookDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <span class="doc-type-badge badge-passbook">Passbook</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Payment Passbook') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-rupee-sign me-1"></i>
                                            <?= number_format($doc['amount'] ?? 0) ?> paid
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/customer/documents/download/passbook/<?= $doc['id'] ?>" class="btn btn-sm btn-success w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Passbook
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Agreements -->
            <div class="col-12">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-file-signature text-warning me-2"></i> Agreements
                </h5>
                <div class="row g-3">
                    <?php if (empty($agreementDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Agreements Found</h6>
                                    <p class="text-muted small">Agreement documents will appear here after booking confirmation.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($agreementDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-warning bg-opacity-10 text-warning">
                                                <i class="fas fa-file-signature"></i>
                                            </div>
                                            <span class="doc-type-badge badge-agreement">Agreement</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Agreement') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-file-signature me-1"></i>
                                            <?= htmlspecialchars($doc['type'] ?? 'Agreement') ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/customer/documents/download/agreement/<?= $doc['id'] ?>" class="btn btn-sm btn-warning w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Agreement
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// Add download tracking if needed
document.querySelectorAll('.document-card a[target="_blank"]').forEach(link => {
    link.addEventListener('click', function() {
        console.log('Document download initiated:', this.href);
    });
});
</script>
</body>
</html>