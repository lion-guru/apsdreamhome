<?php
/**
 * Official A4 Printable Construction & Excavation Permit Certificate
 */
$approval = $approval ?? [];
$token = $approval['permit_token'] ?? 'APS-CNOC-GOR-2026-0000';
$qrUrl = "https://apsdreamhome.com/verify/construction/" . urlencode($token);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'Construction Permit NOC') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46Zmdt9E+9a7k/10/r8g==" crossorigin="anonymous">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #212529; }
        .cert-container { max-width: 820px; margin: 30px auto; background: #fff; padding: 45px 50px; border: 2px solid #0d6efd; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; }
        .watermark { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; font-weight: 900; color: rgba(13, 110, 253, 0.05); text-transform: uppercase; pointer-events: none; white-space: nowrap; }
        .header-brand { border-bottom: 2px double #0d6efd; padding-bottom: 15px; margin-bottom: 25px; }
        .cert-title { font-size: 1.45rem; font-weight: 800; letter-spacing: 1px; color: #0d6efd; text-transform: uppercase; margin-top: 10px; }
        .spec-table th { background: #f1f5f9; width: 35%; font-size: 0.9rem; color: #475569; }
        .spec-table td { font-size: 0.95rem; font-weight: 600; color: #1e293b; }
        .seal-box { border: 1px dashed #94a3b8; height: 90px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #64748b; }
        @media print {
            body { background: #fff; margin: 0; }
            .cert-container { border: 2px solid #000; box-shadow: none; margin: 0; padding: 25px 30px; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-print me-2"></i>Print Official Permit (A4)
    </button>
</div>

<div class="cert-container">
    <div class="watermark">APS PERMIT APPROVED</div>

    <!-- Header -->
    <div class="header-brand text-center">
        <h2 class="fw-black text-primary mb-1">APS DREAM HOME PRIVATE LIMITED</h2>
        <div class="small text-muted">CIN: U70102UP2019PTC118942 | RERA Registered Integrated Township Developer</div>
        <div class="small text-muted">Corporate Office: Medical College Road, Asuran Chowk, Gorakhpur, Uttar Pradesh — 273004</div>
        <div class="cert-title mt-3">ARCHITECTURAL MAP APPROVAL &amp; EXCAVATION PERMIT NOC</div>
        <div class="text-muted small">Issued under Township Building Regulations &amp; Master Plan Bylaws (Gorakhpur)</div>
    </div>

    <!-- Certificate Metadata Bar -->
    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-2 mb-4 border">
        <div>
            <div class="text-muted small">Sanction Permit No.</div>
            <div class="fw-bold font-monospace fs-5 text-primary"><?= htmlspecialchars($token) ?></div>
        </div>
        <div>
            <div class="text-muted small">Sanction Date</div>
            <div class="fw-bold"><?= date('d F Y', strtotime($approval['approval_date'] ?? date('Y-m-d'))) ?></div>
        </div>
        <div>
            <div class="text-muted small">Valid Up To</div>
            <div class="fw-bold text-success"><?= date('d F Y', strtotime($approval['permit_validity_date'] ?? date('Y-m-d', strtotime('+1 year')))) ?></div>
        </div>
        <div class="text-end">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&amp;data=<?= urlencode($qrUrl) ?>" alt="Verification QR" width="65" height="65" class="border p-1 bg-white">
        </div>
    </div>

    <!-- Technical Approval Specifications -->
    <h6 class="fw-bold text-dark text-uppercase mb-2"><i class="fas fa-layer-group me-2 text-primary"></i>1. Plot &amp; Allottee Details</h6>
    <table class="table table-bordered spec-table mb-4">
        <tbody>
            <tr>
                <th scope="row">Allottee / Owner Name</th>
                <td><?= htmlspecialchars($approval['customer_name'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th scope="row">Registered Phone</th>
                <td><?= htmlspecialchars($approval['customer_phone'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th scope="row">Township Colony &amp; Sector</th>
                <td><?= htmlspecialchars($approval['colony_name'] ?? 'APS Township') ?>, Gorakhpur</td>
            </tr>
            <tr>
                <th scope="row">Plot Number &amp; Area</th>
                <td>Plot #<?= htmlspecialchars($approval['plot_number'] ?? 'N/A') ?> &nbsp;|&nbsp; <?= number_format((float)($approval['area_sqft'] ?? 0), 0) ?> Sq.Ft (Facing: <?= htmlspecialchars($approval['facing'] ?? 'Road') ?>)</td>
            </tr>
        </tbody>
    </table>

    <h6 class="fw-bold text-dark text-uppercase mb-2"><i class="fas fa-ruler-combined me-2 text-primary"></i>2. Sanctioned Architectural Parameters</h6>
    <table class="table table-bordered spec-table mb-4">
        <tbody>
            <tr>
                <th scope="row">Building Classification</th>
                <td class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($approval['building_type'] ?? 'residential')) ?></td>
            </tr>
            <tr>
                <th scope="row">Ground Footprint Coverage</th>
                <td><?= number_format((float)($approval['ground_coverage_sqft'] ?? 0), 2) ?> Sq.Ft</td>
            </tr>
            <tr>
                <th scope="row">Sanctioned Built-up Area</th>
                <td><?= number_format((float)($approval['total_builtup_sqft'] ?? 0), 2) ?> Sq.Ft</td>
            </tr>
            <tr>
                <th scope="row">Mandatory Setbacks Adherence</th>
                <td>
                    Front: <strong><?= (float)($approval['front_setback_ft'] ?? 0) ?> Ft</strong> &nbsp;|&nbsp;
                    Rear: <strong><?= (float)($approval['rear_setback_ft'] ?? 0) ?> Ft</strong> &nbsp;|&nbsp;
                    Left: <strong><?= (float)($approval['side_setback_left_ft'] ?? 0) ?> Ft</strong> &nbsp;|&nbsp;
                    Right: <strong><?= (float)($approval['side_setback_right_ft'] ?? 0) ?> Ft</strong>
                </td>
            </tr>
            <tr>
                <th scope="row">Rainwater Harvesting (RWH)</th>
                <td><?= (!empty($approval['rainwater_harvesting_pit'])) ? '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Recharge Pit Mandatory &amp; Sanctioned</span>' : 'Exempted' ?></td>
            </tr>
            <?php if (!empty($approval['architect_name'])): ?>
            <tr>
                <th scope="row">Architect / Consultant</th>
                <td><?= htmlspecialchars($approval['architect_name']) ?> (COA: <?= htmlspecialchars($approval['architect_council_reg'] ?? 'Registered') ?>)</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Mandatory Conditions -->
    <div class="small text-muted mb-4 border-start border-3 border-primary ps-3 py-1">
        <strong>Mandatory Execution Conditions:</strong>
        <ol class="mb-0 ps-3 mt-1">
            <li>No excavation debris or construction aggregate shall obstruct the central township road or roadside storm drain.</li>
            <li>Physical boundary pegs erected by the township survey cell must not be shifted during foundation casting.</li>
            <li>This clearance NOC is valid for 12 months from the date of issue. Construction must commence within this tenure.</li>
        </ol>
    </div>

    <!-- Sign-off Block -->
    <div class="row pt-4 text-center mt-3">
        <div class="col-4">
            <div class="seal-box mb-2">
                <span>[ Official Township Seal ]</span>
            </div>
            <div class="small fw-bold">APS Town Planning Cell</div>
            <div class="small text-muted">Gorakhpur Development Zone</div>
        </div>
        <div class="col-4">
            <div class="seal-box mb-2">
                <span>[ Surveyor &amp; Setback Clearance ]</span>
            </div>
            <div class="small fw-bold"><?= htmlspecialchars($approval['site_engineer_name'] ?? 'Er. Rajesh Srivastava') ?></div>
            <div class="small text-muted">Inspecting Site Engineer</div>
        </div>
        <div class="col-4">
            <div class="seal-box mb-2">
                <span class="text-primary fw-bold">[ Digitally Approved ]</span>
            </div>
            <div class="small fw-bold">Chief Town Planner</div>
            <div class="small text-muted">APS Dream Home Pvt Ltd</div>
        </div>
    </div>
</div>

</body>
</html>
