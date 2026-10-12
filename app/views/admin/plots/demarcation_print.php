<?php
$dem = $dem ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demarcation Certificate — <?= htmlspecialchars($dem['certificate_token'] ?? 'APS-DEM') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #212529; }
        .page-container { max-width: 850px; margin: 30px auto; background: #fff; padding: 45px 50px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
        .watermark { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 65px; font-weight: 900; color: rgba(25, 135, 84, 0.04); text-transform: uppercase; letter-spacing: 6px; pointer-events: none; z-index: 1; user-select: none; }
        .company-header { border-bottom: 2px solid #198754; padding-bottom: 15px; margin-bottom: 25px; }
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
        <a href="/admin/plots/demarcation" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Demarcation Desk
        </a>
        <button onclick="window.print()" class="btn btn-success btn-sm">
            <i class="fas fa-print me-1"></i>Print Demarcation Certificate
        </button>
    </div>

    <!-- Corporate Header -->
    <div class="company-header d-flex justify-content-between align-items-end">
        <div>
            <h3 class="fw-bold text-success mb-1">APS DREAM HOME INFRA DEVELOPERS</h3>
            <p class="text-muted small mb-0">Civil Engineering & Land Survey Department, Gorakhpur (U.P.)</p>
            <p class="text-muted small mb-0">Corporate Office: Medical College Road, Asuran Chowk, Gorakhpur</p>
        </div>
        <div class="text-end">
            <div class="badge bg-success text-uppercase px-3 py-2 mb-1 fs-6">PLOT DEMARCATION CERTIFICATE</div>
            <div class="font-monospace fw-bold small text-dark"><?= htmlspecialchars($dem['certificate_token']) ?></div>
            <div class="text-muted small">Date: <?= date('d M Y', strtotime($dem['demarcation_date'])) ?></div>
        </div>
    </div>

    <!-- Plot Specification Box -->
    <div class="border-box">
        <div class="row g-3">
            <div class="col-6">
                <span class="text-muted text-uppercase small d-block fw-bold">Colony / Township</span>
                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($dem['colony_name']) ?></h5>
                <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($dem['colony_location'] ?? 'Gorakhpur, UP') ?></p>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted text-uppercase small d-block fw-bold">Plot Details</span>
                <h4 class="fw-bold mb-1 text-success">Plot #<?= htmlspecialchars($dem['plot_number']) ?></h4>
                <p class="text-muted small mb-0">Demarcated Area: <strong><?= number_format($dem['area_sqft']) ?> Sq. Ft.</strong></p>
            </div>
        </div>
    </div>

    <!-- Survey Details Grid -->
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-2">Surveyor Identification</span>
                <p class="mb-1 fw-bold text-dark"><i class="fas fa-user-check text-success me-2"></i><?= htmlspecialchars($dem['surveyor_name']) ?></p>
                <p class="mb-1 small"><i class="fas fa-phone text-muted me-2"></i><?= htmlspecialchars($dem['surveyor_phone']) ?></p>
                <p class="mb-0 small text-muted">Inspection Date: <?= date('d F Y', strtotime($dem['demarcation_date'])) ?></p>
            </div>
        </div>
        <div class="col-6">
            <div class="border-box h-100">
                <span class="text-muted text-uppercase small d-block fw-bold mb-2">Pillar Status & Handover</span>
                <p class="mb-1">
                    <span class="badge bg-success px-2 py-1 text-uppercase">
                        <?= ucfirst(str_replace('_', ' ', $dem['pillar_status'])) ?>
                    </span>
                </p>
                <p class="mb-0 small text-muted">
                    Possession Pegs: <strong><?= $dem['possession_peg_handed_over'] ? 'Handed Over On-Site' : 'Pending Physical Handover' ?></strong>
                </p>
                <?php if ($dem['customer_acknowledged']): ?>
                    <p class="mb-0 small text-success mt-1"><i class="fas fa-check-circle me-1"></i>Customer Acknowledged</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 4-Corner WGS84 GPS Coordinates -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark text-uppercase small mb-2 border-bottom pb-1">
            <i class="fas fa-crosshairs text-success me-1"></i>Geodetic 4-Corner GPS Coordinates (WGS84 Reference)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Corner</th>
                        <th>Direction</th>
                        <th>Latitude (WGS84)</th>
                        <th>Longitude (WGS84)</th>
                        <th>Physical Marker</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Corner 1</td>
                        <td>North-West (NW)</td>
                        <td class="font-monospace text-primary"><?= $dem['corner_nw_lat'] ? number_format($dem['corner_nw_lat'], 7) : 'Pending Geotag' ?></td>
                        <td class="font-monospace text-primary"><?= $dem['corner_nw_lng'] ? number_format($dem['corner_nw_lng'], 7) : 'Pending Geotag' ?></td>
                        <td>Reinforced Concrete Pillar</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Corner 2</td>
                        <td>North-East (NE)</td>
                        <td class="font-monospace text-primary"><?= $dem['corner_ne_lat'] ? number_format($dem['corner_ne_lat'], 7) : 'Pending Geotag' ?></td>
                        <td class="font-monospace text-primary"><?= $dem['corner_ne_lng'] ? number_format($dem['corner_ne_lng'], 7) : 'Pending Geotag' ?></td>
                        <td>Reinforced Concrete Pillar</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Corner 3</td>
                        <td>South-West (SW)</td>
                        <td class="font-monospace text-primary"><?= $dem['corner_sw_lat'] ? number_format($dem['corner_sw_lat'], 7) : 'Pending Geotag' ?></td>
                        <td class="font-monospace text-primary"><?= $dem['corner_sw_lng'] ? number_format($dem['corner_sw_lng'], 7) : 'Pending Geotag' ?></td>
                        <td>Reinforced Concrete Pillar</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Corner 4</td>
                        <td>South-East (SE)</td>
                        <td class="font-monospace text-primary"><?= $dem['corner_se_lat'] ? number_format($dem['corner_se_lat'], 7) : 'Pending Geotag' ?></td>
                        <td class="font-monospace text-primary"><?= $dem['corner_se_lng'] ? number_format($dem['corner_se_lng'], 7) : 'Pending Geotag' ?></td>
                        <td>Reinforced Concrete Pillar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Boundary Notes -->
    <?php if (!empty($dem['boundary_notes'])): ?>
        <div class="border-box mb-4">
            <span class="text-muted text-uppercase small d-block fw-bold mb-1">Surveyor On-Site Observations</span>
            <p class="mb-0 small text-dark fst-italic">"<?= nl2br(htmlspecialchars($dem['boundary_notes'])) ?>"</p>
        </div>
    <?php endif; ?>

    <!-- Certificate Terms -->
    <div class="small text-muted mb-4 border-top pt-3">
        <p class="fw-bold text-dark mb-1">Statutory & Engineering Declaration:</p>
        <ol class="ps-3 mb-0" style="font-size: 11px;">
            <li>This certificate certifies that boundary pegs/pillars have been physically erected and verified as per the approved township layout map.</li>
            <li>Coordinates provided above are logged using handheld differential GPS under WGS84 datum and constitute authentic corner demarcation.</li>
            <li>Any physical tampering, shifting, or removal of concrete boundary markers without written clearance from the developer is strictly prohibited.</li>
        </ol>
    </div>

    <!-- Sign-off Block -->
    <div class="row pt-4 mt-3 border-top text-center">
        <div class="col-4">
            <div style="height: 45px;"></div>
            <p class="border-top pt-1 small fw-bold mb-0 text-dark"><?= htmlspecialchars($dem['surveyor_name']) ?></p>
            <span class="text-muted" style="font-size: 11px;">Licensed Site Surveyor</span>
        </div>
        <div class="col-4">
            <div style="height: 45px;"></div>
            <p class="border-top pt-1 small fw-bold mb-0 text-dark">Executive Engineer (Civil)</p>
            <span class="text-muted" style="font-size: 11px;">Township Quality Wing</span>
        </div>
        <div class="col-4">
            <div style="height: 45px;"></div>
            <p class="border-top pt-1 small fw-bold mb-0 text-dark">Allottee / Possession Holder</p>
            <span class="text-muted" style="font-size: 11px;">Physical Peg Handover Acceptance</span>
        </div>
    </div>
</div>

</body>
</html>
