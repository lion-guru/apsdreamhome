<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order — <?= htmlspecialchars($order['order_number'] ?? 'WO') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #212529; }
        .page-container { max-width: 850px; margin: 30px auto; background: #fff; padding: 45px 50px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
        .watermark { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 70px; font-weight: 900; color: rgba(13, 110, 253, 0.04); text-transform: uppercase; letter-spacing: 6px; pointer-events: none; z-index: 1; user-select: none; }
        .company-header { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 25px; }
        .border-box { border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; background: #fdfdfd; margin-bottom: 20px; }
        .table-condensed th, .table-condensed td { padding: 6px 10px; font-size: 13px; }
        @media print {
            body { background: #fff; }
            .page-container { margin: 0; box-shadow: none; padding: 25px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="watermark">APS DREAM HOME</div>

    <!-- Print Action Bar -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <a href="/admin/work-orders/<?= $order['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Order Details
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print me-1"></i>Print / Save as PDF
        </button>
    </div>

    <!-- Corporate Header -->
    <div class="company-header d-flex justify-content-between align-items-end">
        <div>
            <h3 class="fw-bold text-primary mb-1">APS DREAM HOME INFRA DEVELOPERS</h3>
            <p class="text-muted small mb-0">Corporate Office: Medical College Road, Asuran Chowk, Gorakhpur (U.P.)</p>
            <p class="text-muted small mb-0">CIN: U70102UP2020PTC123456 | GSTIN: 09AAACA1234F1Z5</p>
        </div>
        <div class="text-end">
            <div class="badge bg-primary text-uppercase px-3 py-2 mb-1 fs-6">OFFICIAL WORK ORDER</div>
            <div class="font-monospace fw-bold small text-dark"><?= htmlspecialchars($order['order_number']) ?></div>
            <div class="text-muted small">Date: <?= date('d M Y', strtotime($order['created_at'])) ?></div>
        </div>
    </div>

    <!-- Order Summary Grid -->
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-2">Issued To (Contractor)</span>
                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($order['contractor_name']) ?></h5>
                <p class="mb-1 small"><i class="fas fa-phone-alt text-muted me-1"></i><?= htmlspecialchars($order['contractor_phone']) ?></p>
                <?php if (!empty($order['contractor_pan'])): ?>
                    <p class="mb-0 small"><span class="text-muted">PAN:</span> <?= htmlspecialchars($order['contractor_pan']) ?></p>
                <?php endif; ?>
                <?php if (!empty($order['contractor_gstin'])): ?>
                    <p class="mb-0 small"><span class="text-muted">GSTIN:</span> <?= htmlspecialchars($order['contractor_gstin']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-2">Project & Site Location</span>
                <h5 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($order['colony_name']) ?></h5>
                <p class="mb-1 small text-muted"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($order['colony_location'] ?: 'Gorakhpur, U.P.') ?></p>
                <p class="mb-0 small"><span class="text-muted">Category:</span> <strong class="text-uppercase"><?= str_replace('_', ' ', htmlspecialchars($order['work_category'])) ?></strong></p>
                <p class="mb-0 small"><span class="text-muted">Target Completion:</span> <?= !empty($order['completion_deadline']) ? date('d M Y', strtotime($order['completion_deadline'])) : 'As agreed' ?></p>
            </div>
        </div>
    </div>

    <!-- Contract Scope -->
    <div class="border-box mb-3">
        <span class="text-muted text-uppercase small d-block fw-bold mb-2">Work Specification & Scope of Execution</span>
        <h6 class="fw-bold text-dark"><?= htmlspecialchars($order['work_title']) ?></h6>
        <p class="small text-secondary mb-0"><?= nl2br(htmlspecialchars($order['scope_description'] ?: 'Execution of civil works according to company approved architectural layout and site engineering standards.')) ?></p>
    </div>

    <!-- Financial Terms -->
    <table class="table table-bordered table-condensed mb-4">
        <thead class="table-light">
            <tr>
                <th>Description</th>
                <th class="text-end" style="width: 200px;">Amount / Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Contract Consideration (All Inclusive)</strong></td>
                <td class="text-end fw-bold fs-6">₹<?= number_format((float)$order['contract_amount'], 2) ?></td>
            </tr>
            <tr>
                <td>Contractual Retention Holdback (Released post 6-month defect liability period)</td>
                <td class="text-end text-warning fw-bold"><?= $order['retention_pct'] ?>%</td>
            </tr>
            <tr>
                <td>Statutory TDS Deduction (Income Tax Act Sec 194C)</td>
                <td class="text-end text-info fw-bold"><?= $order['tds_pct'] ?>%</td>
            </tr>
        </tbody>
    </table>

    <!-- Terms & Conditions -->
    <div class="small text-muted mb-4 border-start border-3 border-primary ps-3">
        <p class="fw-bold mb-1 text-dark">Terms of Work Execution:</p>
        <ol class="mb-0 ps-3">
            <li>Payments shall be released against Running Account (RA) bills supported by Site Engineer measurement book (MB) verification.</li>
            <li>Retention money (<?= $order['retention_pct'] ?>%) will be withheld from each RA bill and released post 180 days of final completion.</li>
            <li>All materials used must conform strictly to ISI / BIS specifications (cement, steel, aggregate).</li>
            <li>Contractor is solely liable for on-site laborer safety, insurance, and compliance with statutory labor laws.</li>
        </ol>
    </div>

    <!-- Signatures -->
    <div class="row pt-4 mt-4 border-top">
        <div class="col-6 text-center">
            <div style="height: 60px;"></div>
            <p class="border-top pt-2 mb-0 fw-bold small text-dark">Contractor Acceptance Signature & Seal</p>
            <small class="text-muted"><?= htmlspecialchars($order['contractor_name']) ?></small>
        </div>
        <div class="col-6 text-center">
            <div style="height: 60px;"></div>
            <p class="border-top pt-2 mb-0 fw-bold small text-dark">Authorized Signatory</p>
            <small class="text-muted">APS Dream Home Infra Developers Pvt. Ltd.</small>
        </div>
    </div>
</div>

</body>
</html>
