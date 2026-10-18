<?php
/**
 * Allotment Letter — Customer Portal Fallback View
 * Used when PDF generation fails or file not found
 */
$doc = $doc ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allotment Letter - APS Dream Home</title>
    <link href="<?php echo  BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo  BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
        }
        .letter-header { border-bottom: 2px solid #0d9488; padding-bottom: 20px; margin-bottom: 30px; }
        .letter-body { line-height: 1.8; }
        .detail-row { display: flex; margin-bottom: 12px; }
        .detail-label { width: 200px; font-weight: 600; color: #374151; }
        .detail-value { flex: 1; color: #1f2937; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="no-print text-center mb-4">
            <a href="<?php echo  BASE_URL ?>/user/documents" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Documents
            </a>
            <button class="btn btn-primary ms-2 no-print" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print / Save as PDF
            </button>
        </div>

        <div class="letter-header text-center">
            <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                <i class="fas fa-home fa-2x text-primary"></i>
                <div>
                    <h2 class="mb-0 fw-bold">APS Dream Home</h2>
                    <small class="text-muted">Registered Office: Gorakhpur, Uttar Pradesh</small>
                </div>
            </div>
            <h3 class="fw-bold text-primary mb-1">ALLOTMENT LETTER</h3>
            <p class="text-muted mb-0">Ref: AL-<?php echo  htmlspecialchars($doc['allotment_number'] ?? 'AL-' . str_pad($doc['id'] ?? 0, 6, '0', STR_PAD_LEFT)) ?></p>
        </div>

        <div class="letter-body">
            <p class="text-center mb-4"><strong>Date:</strong> <?php echo  htmlspecialchars($doc['issue_date'] ?? date('d M Y')) ?></p>

            <p>Dear <strong><?php echo  htmlspecialchars($doc['customer_name'] ?? 'Valued Customer') ?></strong>,</p>

            <p>We are pleased to inform you that the following property has been allotted to you as per the terms and conditions agreed upon:</p>

            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">Property Details</div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Booking Number:</span>
                        <span class="detail-value"><?php echo  htmlspecialchars($doc['booking_number'] ?? '—') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Plot Number:</span>
                        <span class="detail-value"><?php echo  htmlspecialchars($doc['plot_number'] ?? '—') ?></span>
                    </div>
                    <?php if (!empty($doc['gata_number'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Gata / Khasra:</span>
                        <span class="detail-value"><?php echo  htmlspecialchars($doc['gata_number']) ?><?= !empty($doc['gata_owner']) ? ' (' . htmlspecialchars($doc['gata_owner']) . ')' : '' ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="detail-label">Colony:</span>
                        <span class="detail-value"><?php echo  htmlspecialchars($doc['colony_name'] ?? '—') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Plot Area:</span>
                        <span class="detail-value"><?php echo  htmlspecialchars($doc['plot_area'] ?? '—') ?> sq. ft.</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Consideration:</span>
                        <span class="detail-value text-primary fw-bold">₹<?php echo  htmlspecialchars(number_format($doc['total_amount'] ?? 0, 2)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Amount Paid:</span>
                        <span class="detail-value text-success fw-bold">₹<?php echo  htmlspecialchars(number_format($doc['paid_amount'] ?? 0, 2)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Balance Due:</span>
                        <span class="detail-value text-danger fw-bold">₹<?php echo  htmlspecialchars(number_format(($doc['total_amount'] ?? 0) - ($doc['paid_amount'] ?? 0), 2)) ?></span>
                    </div>
                </div>
            </div>

            <p><strong>Terms & Conditions:</strong></p>
            <ol class="small text-muted">
                <li>This allotment is subject to the terms of the Booking Agreement and the Tripartite Master Deed.</li>
                <li>Possession will be handed over upon full payment of all dues and execution of the Sale Deed.</li>
                <li>The allottee shall comply with all RERA regulations and local municipal bylaws.</li>
                <li>Any change in the allottee's details must be communicated in writing with supporting documents.</li>
            </ol>

            <div class="mt-5 pt-3 border-top">
                <p class="mb-1"><strong>For APS Dream Home</strong></p>
                <p class="text-muted small">Authorized Signatory</p>
            </div>
        </div>

        <div class="no-print text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print / Save as PDF
            </button>
        </div>
    </div>
</body>
</html>