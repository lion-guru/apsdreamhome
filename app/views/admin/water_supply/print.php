<?php
/**
 * A4 Printable Township Borewell Pumping & Potable Water Supply Certificate
 */
$log = $log ?? [];
$quality = $quality ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>APS Dream Home — Water Pumping Slip #<?= $log['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; }
        .water-slip { max-width: 800px; margin: 20px auto; border: 2px solid #0d6efd; padding: 28px; border-radius: 8px; }
        .header-logo { font-size: 24px; font-weight: 800; color: #0d6efd; letter-spacing: 0.5px; }
        .watermark { position: absolute; opacity: 0.05; font-size: 110px; font-weight: 900; top: 40%; left: 15%; transform: rotate(-25deg); pointer-events: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .water-slip { border: 1px solid #333; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="water-slip position-relative">
    <div class="watermark">APS WATER</div>
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <div class="header-logo"><i class="fas fa-water me-2"></i>APS DREAM HOME TOWNSHIPS</div>
            <div class="small text-muted">Civil Infrastructure &amp; Public Health Engineering Department</div>
            <div class="small text-muted">Gorakhpur, Uttar Pradesh — 273001</div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 px-3 py-2">LOG #<?= str_pad($log['id'], 5, '0', STR_PAD_LEFT) ?></span>
            <div class="small text-muted mt-1">Generated: <?= date('d M Y, h:i A') ?></div>
        </div>
    </div>

    <div class="alert <?= $quality['is_potable'] ? 'alert-success' : 'alert-warning' ?> d-flex justify-content-between align-items-center py-2 mb-4">
        <div>
            <strong><i class="fas fa-check-circle me-1"></i> Potable Water Quality Status:</strong>
            <?= $quality['is_potable'] ? 'Compliant with BIS:10500 Drinking Water Standards' : 'Requires Filter Backwash / Filtration Review' ?>
        </div>
        <span class="badge bg-dark">TDS: <?= (int)$log['tds_ppm'] ?> PPM | pH: <?= number_format((float)$log['ph_level'], 1) ?></span>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th class="table-light w-25">Township / Colony:</th>
                <td class="w-25 fw-bold text-primary"><?= htmlspecialchars($log['colony_name'] ?? 'Colony #' . $log['colony_id']) ?></td>
                <th class="table-light w-25">Log Date &amp; Shift:</th>
                <td class="w-25 fw-bold"><?= htmlspecialchars($log['log_date']) ?> (<?= ucfirst($log['shift']) ?>)</td>
            </tr>
            <tr>
                <th class="table-light">Borewell Pump Station:</th>
                <td><?= htmlspecialchars($log['pump_number']) ?></td>
                <th class="table-light">Pumping Hours:</th>
                <td><?= $log['pump_start_time'] ? substr($log['pump_start_time'], 0, 5) : '--' ?> to <?= $log['pump_stop_time'] ? substr($log['pump_stop_time'], 0, 5) : '--' ?> (<?= number_format((float)$log['run_hours'], 1) ?> hrs)</td>
            </tr>
            <tr>
                <th class="table-light">Water Pumped:</th>
                <td class="fw-bold text-success fs-6"><?= number_format((float)$log['water_pumped_kl'], 1) ?> kL (<?= number_format((float)$log['water_pumped_kl'] * 1000) ?> Litres)</td>
                <th class="table-light">Power Consumed:</th>
                <td><?= number_format((float)$log['power_units_kwh'], 1) ?> kWh Units</td>
            </tr>
            <tr>
                <th class="table-light">Overhead Tank (OHT) Level:</th>
                <td><?= (int)$log['oht_level_pct'] ?>% Capacity</td>
                <th class="table-light">Line Pressure:</th>
                <td><?= number_format((float)$log['line_pressure_bar'], 2) ?> Bar</td>
            </tr>
            <tr>
                <th class="table-light">Chlorination Dosing:</th>
                <td><?= number_format((float)$log['chlorine_dose_gm'], 1) ?> grams</td>
                <th class="table-light">Pump Mechanical Health:</th>
                <td class="text-capitalize fw-semibold"><?= str_replace('_', ' ', $log['pump_health']) ?></td>
            </tr>
            <tr>
                <th class="table-light">Station Operator:</th>
                <td><?= htmlspecialchars($log['operator_name']) ?> (<?= htmlspecialchars($log['operator_phone'] ?? 'N/A') ?>)</td>
                <th class="table-light">Observations / Notes:</th>
                <td><?= htmlspecialchars($log['remarks'] ?: 'All feeder lines pressurized normally.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row text-center mt-5 pt-4 border-top">
        <div class="col-4">
            <div class="small fw-bold">Operator / Attendant</div>
            <div class="text-muted small mt-4"><?= htmlspecialchars($log['operator_name']) ?></div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">PHE Maintenance Engineer</div>
            <div class="text-muted small mt-4">Verified &amp; Inspected</div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">Township Manager / S.O.</div>
            <div class="text-muted small mt-4">Authorized Signature &amp; Stamp</div>
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="fas fa-print me-1"></i> Print Certificate</button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4">Close</button>
    </div>
</div>
</body>
</html>
