<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRO Registry Gatepass — <?= htmlspecialchars($appointment['appointment_token'] ?? 'REG') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #212529; }
        .page-container { max-width: 800px; margin: 30px auto; background: #fff; padding: 45px 50px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
        .watermark { position: absolute; top: 48%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 70px; font-weight: 900; color: rgba(220, 53, 69, 0.04); text-transform: uppercase; letter-spacing: 6px; pointer-events: none; z-index: 1; user-select: none; }
        .header-bar { border-bottom: 2px solid #dc3545; padding-bottom: 15px; margin-bottom: 20px; }
        .border-box { border: 1px solid #dee2e6; border-radius: 4px; padding: 12px 15px; background: #fdfdfd; margin-bottom: 15px; }
        @media print {
            body { background: #fff; }
            .page-container { margin: 0; box-shadow: none; padding: 25px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="watermark">APS REGISTRY PASS</div>

    <!-- Print Action Bar -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </button>
        <button onclick="window.print()" class="btn btn-danger btn-sm">
            <i class="fas fa-print me-1"></i>Print Official Gatepass
        </button>
    </div>

    <!-- Corporate Header -->
    <div class="header-bar d-flex justify-content-between align-items-end">
        <div>
            <h3 class="fw-bold text-danger mb-1">APS DREAM HOME INFRA DEVELOPERS</h3>
            <p class="text-muted small mb-0">Sub-Registrar Office Legal Coordination Desk</p>
            <p class="text-muted small mb-0">Gorakhpur Corporate Office: Medical College Road, Asuran Chowk</p>
        </div>
        <div class="text-end">
            <div class="badge bg-danger text-uppercase px-3 py-2 mb-1 fs-6">SRO REGISTRY GATEPASS</div>
            <div class="font-monospace fw-bold small text-dark"><?= htmlspecialchars($appointment['appointment_token']) ?></div>
            <div class="text-muted small">Date: <?= date('d M Y') ?></div>
        </div>
    </div>

    <!-- Appointment Slot Highlight Box -->
    <div class="p-3 bg-light border border-2 border-danger rounded-3 mb-3 text-center">
        <span class="text-muted text-uppercase small d-block fw-bold">Confirmed SRO Appointment Slot</span>
        <h4 class="fw-bold text-danger mb-1">
            <i class="far fa-calendar-check me-2"></i><?= !empty($appointment['confirmed_date']) ? date('l, d F Y', strtotime($appointment['confirmed_date'])) : date('l, d F Y', strtotime($appointment['preferred_date_1'])) ?>
        </h4>
        <p class="mb-0 small fw-semibold text-dark">
            <i class="fas fa-landmark text-primary me-1"></i>Venue: <?= htmlspecialchars($appointment['sro_office']) ?> (Gorakhpur)
        </p>
    </div>

    <!-- Property & Buyer Grid -->
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-1">Buyer Details</span>
                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($appointment['customer_name']) ?></h6>
                <p class="small text-muted mb-0"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($appointment['customer_phone']) ?></p>
            </div>
        </div>
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-1">Plot Details</span>
                <h6 class="fw-bold text-primary mb-1"><?= htmlspecialchars($appointment['colony_name']) ?></h6>
                <p class="small text-dark mb-0">Plot #<strong><?= htmlspecialchars($appointment['plot_number']) ?></strong> (<?= htmlspecialchars($appointment['area_sqft'] ?? '') ?> sq.ft)</p>
            </div>
        </div>
    </div>

    <!-- Legal Executive & Witnesses -->
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-1">APS Legal Executive (At Tehsil)</span>
                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($appointment['assigned_executive_name'] ?: 'Shri Rajesh Tripathi (Legal Desk)') ?></h6>
                <p class="small text-muted mb-0"><i class="fas fa-phone-alt text-success me-1"></i><?= htmlspecialchars($appointment['assigned_executive_phone'] ?: '+91 98390 12345') ?></p>
            </div>
        </div>
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-1">Witness Details</span>
                <p class="small text-dark mb-1">1. <?= htmlspecialchars($appointment['witness_1_name'] ?: 'Witness 1 Provided on Site') ?></p>
                <p class="small text-dark mb-0">2. <?= htmlspecialchars($appointment['witness_2_name'] ?: 'Witness 2 Provided on Site') ?></p>
            </div>
        </div>
    </div>

    <!-- Mandatory Checklist -->
    <div class="border-box mb-4">
        <span class="text-danger text-uppercase small d-block fw-bold mb-2">Mandatory Document Checklist for Sub-Registrar Office</span>
        <ul class="small mb-0 ps-3">
            <li>Original Aadhaar Card and PAN Card of Buyer (plus 2 self-attested photocopies).</li>
            <li>Original Aadhaar Cards of both witnesses (must be physically present with thumb impression).</li>
            <li>4 Passport-size recent color photographs with white background.</li>
            <li>Official Registry NOC and No-Dues Certificate issued by APS Dream Home.</li>
            <li>e-Stamp Paper / e-Grass Challan Receipt (Stamp Duty: ₹<?= number_format((float)$appointment['stamp_duty_amount'], 2) ?>).</li>
        </ul>
    </div>

    <!-- Instructions Notice -->
    <div class="small text-muted mb-4 p-2 bg-light rounded text-center">
        Please report to the SRO Office by 10:30 AM on the scheduled date. In case of rescheduling, notify 24 hours prior.
    </div>

    <!-- Signatures -->
    <div class="row pt-4 border-top">
        <div class="col-6 text-center">
            <p class="mb-0 fw-bold small text-dark">Buyer Signature</p>
            <small class="text-muted"><?= htmlspecialchars($appointment['customer_name']) ?></small>
        </div>
        <div class="col-6 text-center">
            <p class="mb-0 fw-bold small text-dark">Authorized Legal Officer</p>
            <small class="text-muted">APS Dream Home Infra Developers Pvt. Ltd.</small>
        </div>
    </div>
</div>

</body>
</html>
