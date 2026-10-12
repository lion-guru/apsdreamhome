<?php
/**
 * A4 Printable Central DG Genset Power Backup & Diesel Consumption Audit Certificate
 */
$log = $log ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>APS Dream Home — DG Run Certificate #<?= $log['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; }
        .genset-slip { max-width: 800px; margin: 20px auto; border: 2px solid #ffc107; padding: 28px; border-radius: 8px; }
        .header-logo { font-size: 24px; font-weight: 800; color: #b45309; letter-spacing: 0.5px; }
        .watermark { position: absolute; opacity: 0.05; font-size: 110px; font-weight: 900; top: 40%; left: 15%; transform: rotate(-25deg); pointer-events: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .genset-slip { border: 1px solid #333; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="genset-slip position-relative">
    <div class="watermark">APS GENSET</div>
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <div class="header-logo"><i class="fas fa-charging-station me-2"></i>APS DREAM HOME TOWNSHIPS</div>
            <div class="small text-muted">Central Power Backup &amp; Electrification Engineering Cell</div>
            <div class="small text-muted">Gorakhpur, Uttar Pradesh — 273001</div>
        </div>
        <div class="text-end">
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">CERTIFICATE #<?= str_pad($log['id'], 5, '0', STR_PAD_LEFT) ?></span>
            <div class="small text-muted mt-1">Generated: <?= date('d M Y, h:i A') ?></div>
        </div>
    </div>

    <div class="alert alert-warning d-flex justify-content-between align-items-center py-2 mb-4 text-dark">
        <div>
            <strong><i class="fas fa-bolt me-1"></i> Power Outage Reason:</strong>
            <?= ucwords(str_replace('_', ' ', $log['outage_reason'])) ?>
        </div>
        <span class="badge bg-dark">Run: <?= number_format((float)$log['run_hours'], 1) ?> hrs | Diesel: <?= number_format((float)$log['diesel_consumed_litres'], 1) ?> L</span>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th class="table-light w-25">Township / Colony:</th>
                <td class="w-25 fw-bold text-primary"><?= htmlspecialchars($log['colony_name'] ?? 'Colony #' . $log['colony_id']) ?></td>
                <th class="table-light w-25">Date of Operation:</th>
                <td class="w-25 fw-bold"><?= htmlspecialchars($log['log_date']) ?></td>
            </tr>
            <tr>
                <th class="table-light">DG Unit Specification:</th>
                <td><?= htmlspecialchars($log['genset_number']) ?></td>
                <th class="table-light">Operating Window:</th>
                <td><?= $log['start_time'] ? substr($log['start_time'], 0, 5) : '--' ?> to <?= $log['stop_time'] ? substr($log['stop_time'], 0, 5) : '--' ?></td>
            </tr>
            <tr>
                <th class="table-light">Diesel Consumed:</th>
                <td class="fw-bold text-danger fs-6"><?= number_format((float)$log['diesel_consumed_litres'], 1) ?> Litres</td>
                <th class="table-light">Fuel Expense Incurred:</th>
                <td class="fw-bold text-dark fs-6">₹<?= number_format((float)$log['diesel_expense_inr'], 2) ?> (@ ₹<?= number_format((float)$log['diesel_rate_per_litre'], 2) ?>/L)</td>
            </tr>
            <tr>
                <th class="table-light">Power Generated:</th>
                <td><?= number_format((float)$log['power_generated_kwh'], 1) ?> kWh Units</td>
                <th class="table-light">Efficiency Index:</th>
                <td><?= number_format((float)$log['fuel_efficiency_kwh_per_litre'], 2) ?> kWh / Litre</td>
            </tr>
            <tr>
                <th class="table-light">Closing Diesel Stock:</th>
                <td><?= number_format((float)$log['diesel_closing_stock_litres'], 1) ?> Litres in Tank</td>
                <th class="table-light">Engine Mechanical Health:</th>
                <td class="text-capitalize fw-semibold"><?= str_replace('_', ' ', $log['mechanical_status']) ?></td>
            </tr>
            <tr>
                <th class="table-light">Attendant / Operator:</th>
                <td><?= htmlspecialchars($log['operator_name']) ?> (<?= htmlspecialchars($log['operator_phone'] ?? 'N/A') ?>)</td>
                <th class="table-light">Observations / Remarks:</th>
                <td><?= htmlspecialchars($log['remarks'] ?: 'Township critical loads maintained smoothly.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row text-center mt-5 pt-4 border-top">
        <div class="col-4">
            <div class="small fw-bold">DG Attendant</div>
            <div class="text-muted small mt-4"><?= htmlspecialchars($log['operator_name']) ?></div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">Electrical Site Engineer</div>
            <div class="text-muted small mt-4">Verified &amp; Inspected</div>
        </div>
        <div class="col-4">
            <div class="small fw-bold">Accounts / Maintenance Head</div>
            <div class="text-muted small mt-4">Fuel Reconciled &amp; Signed</div>
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-warning px-4 me-2"><i class="fas fa-print me-1"></i> Print Certificate</button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4">Close</button>
    </div>
</div>
</body>
</html>
