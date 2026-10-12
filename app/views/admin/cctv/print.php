<?php
/**
 * A4 Printable Central CCTV Surveillance & Camera Uptime Audit Certificate
 */
$log = $log ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>APS Dream Home — CCTV Audit Certificate #<?= $log['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; }
        .cctv-slip { max-width: 800px; margin: 20px auto; border: 2px solid #0d6efd; padding: 28px; border-radius: 8px; }
        .header-logo { font-size: 24px; font-weight: 800; color: #0d6efd; letter-spacing: 0.5px; }
        .watermark { position: absolute; opacity: 0.05; font-size: 110px; font-weight: 900; top: 40%; left: 15%; transform: rotate(-25deg); pointer-events: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .cctv-slip { border: 1px solid #333; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="cctv-slip position-relative">
    <div class="watermark">APS CCTV</div>
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <div class="header-logo"><i class="fas fa-video me-2"></i>APS DREAM HOME TOWNSHIPS</div>
            <div class="small text-muted">Central Security Surveillance &amp; IT Engineering Cell</div>
            <div class="small text-muted">Gorakhpur, Uttar Pradesh — 273001</div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 px-3 py-2">AUDIT #<?= str_pad($log['id'], 5, '0', STR_PAD_LEFT) ?></span>
            <div class="small text-muted mt-1">Generated: <?= date('d M Y, h:i A') ?></div>
        </div>
    </div>

    <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-4">
        <div>
            <strong><i class="fas fa-camera me-1"></i> System Uptime Status:</strong>
            <?= (float)$log['uptime_percentage'] >= 90 ? 'Optimal Security Monitoring Level' : 'Attention: Camera Feed Redundancy Reduced' ?>
        </div>
        <span class="badge bg-dark">Uptime: <?= number_format((float)$log['uptime_percentage'], 1) ?>% (<?= (int)$log['cameras_online_count'] ?> / <?= (int)$log['total_cameras_installed'] ?> Online)</span>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th class="table-light w-25">Township / Colony:</th>
                <td class="w-25 fw-bold text-primary"><?= htmlspecialchars($log['colony_name'] ?? 'Colony #' . $log['colony_id']) ?></td>
                <th class="table-light w-25">Audit Date:</th>
                <td class="w-25 fw-bold"><?= htmlspecialchars($log['audit_date']) ?></td>
            </tr>
            <tr>
                <th class="table-light">NVR Station Unit:</th>
                <td><?= htmlspecialchars($log['nvr_identifier']) ?></td>
                <th class="table-light">Recording Retention:</th>
                <td><?= (int)$log['recording_retention_days'] ?> Days Local Storage</td>
            </tr>
            <tr>
                <th class="table-light">NVR HDD Storage:</th>
                <td class="text-capitalize fw-semibold"><?= str_replace('_', ' ', $log['hdd_storage_status']) ?></td>
                <th class="table-light">Night Vision IR Status:</th>
                <td class="text-capitalize"><?= str_replace('_', ' ', $log['night_vision_ir_status']) ?></td>
            </tr>
            <tr>
                <th class="table-light">Offline / Blind Spots:</th>
                <td colspan="3" class="text-danger fw-semibold"><?= htmlspecialchars($log['offline_camera_locations'] ?: 'None (100% Perimeter Coverage)') ?></td>
            </tr>
            <tr>
                <th class="table-light">Footage Exports Today:</th>
                <td><?= (int)$log['footage_exported_count'] ?> incident clips logged</td>
                <th class="table-light">Auditing Technician:</th>
                <td><?= htmlspecialchars($log['cctv_technician_name']) ?> (<?= htmlspecialchars($log['technician_phone'] ?? 'N/A') ?>)</td>
            </tr>
            <tr>
                <th class="table-light">Observations / Remarks:</th>
                <td colspan="3"><?= htmlspecialchars($log['remarks'] ?: 'Central control room feeds inspected and confirmed functional.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row text-center mt-5 pt-4 border-top">
        <div class="col-4">
            <div class="small fw-bold">Surveillance Technician</div>
            <div class="text-muted small mt-4"><?= htmlspecialchars($log['cctv_technician_name']) ?></div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">Chief Security Officer (CSO)</div>
            <div class="text-muted small mt-4">Verified &amp; Inspected</div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">Township Manager / S.O.</div>
            <div class="text-muted small mt-4">Authorized Signature &amp; Seal</div>
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="fas fa-print me-1"></i> Print Certificate</button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4">Close</button>
    </div>
</div>
</body>
</html>
