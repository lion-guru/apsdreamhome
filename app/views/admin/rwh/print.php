<?php
$log = $log ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rainwater Harvesting & Ground Water Compliance Certificate - #<?= (int)($log['id'] ?? 0) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; background: #fff; }
        .cert-container { border: 3px double #cbd5e1; padding: 35px; position: relative; max-width: 820px; margin: 0 auto; min-height: 980px; box-sizing: border-box; }
        .watermark { position: absolute; top: 40%; left: 10%; font-size: 55px; color: rgba(2, 132, 199, 0.05); transform: rotate(-30deg); font-weight: 800; pointer-events: none; z-index: 0; text-transform: uppercase; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 24px; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .header h3 { margin: 5px 0; font-size: 14px; color: #0284c7; font-weight: 600; text-transform: uppercase; }
        .header p { margin: 0; font-size: 11px; color: #64748b; }
        .cert-title { text-align: center; margin: 25px 0; }
        .cert-title h2 { margin: 0; font-size: 17px; background: #e0f2fe; color: #0369a1; display: inline-block; padding: 6px 20px; border-radius: 4px; border: 1px solid #bae6fd; letter-spacing: 0.5px; }
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .grid-table th, .grid-table td { border: 1px solid #e2e8f0; padding: 9px 12px; text-align: left; }
        .grid-table th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; width: 35%; }
        .grid-table td { color: #0f172a; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .footer-signatures { margin-top: 60px; display: flex; justify-content: space-between; text-align: center; }
        .sig-block { width: 220px; border-top: 1px solid #475569; padding-top: 6px; font-size: 11px; font-weight: bold; }
        @media print { .no-print { display: none; } body { padding: 0; } .cert-container { border: 2px solid #000; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; font-size: 14px; background: #0284c7; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Print RWH & Groundwater Certificate
        </button>
    </div>

    <div class="cert-container">
        <div class="watermark">APS GROUNDWATER RECHARGE</div>

        <div class="header">
            <h1>APS DREAM HOME PRIVATE LIMITED</h1>
            <h3>Environmental Engineering & Groundwater Conservation Cell</h3>
            <p>Corporate Office: Civil Lines, Gorakhpur, Uttar Pradesh | Central Ground Water Authority (CGWA) & UP Groundwater Act Compliant</p>
        </div>

        <div class="cert-title">
            <h2>CENTRAL RAINWATER HARVESTING & GROUNDWATER RECHARGE AUDIT CERTIFICATE</h2>
        </div>

        <table class="grid-table">
            <tr>
                <th>Certificate Ref ID</th>
                <td><strong>APS-RWH-<?= str_pad((string)$log['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
            </tr>
            <tr>
                <th>Township Colony</th>
                <td><strong><?= htmlspecialchars($log['colony_name'] ?? 'N/A') ?></strong> (<?= htmlspecialchars($log['district_name'] ?? 'Gorakhpur') ?>)</td>
            </tr>
            <tr>
                <th>Audit Date</th>
                <td><strong><?= date('d F Y', strtotime($log['audit_date'])) ?></strong></td>
            </tr>
            <tr>
                <th>Recharge Shaft / Pit Ref</th>
                <td><strong><?= htmlspecialchars($log['pit_identifier'] ?? 'Pit-1') ?></strong></td>
            </tr>
            <tr>
                <th>Recharge Structure Type</th>
                <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['recharge_type'] ?? 'borewell_recharge_shaft'))) ?></td>
            </tr>
            <tr>
                <th>Catchment Area Serviced</th>
                <td><strong><?= number_format((float)($log['catchment_area_sqft'] ?? 0)) ?> Sq.Ft</strong> (Paved Avenue & Rooftop Runoff)</td>
            </tr>
            <tr>
                <th>Daily Recharge Capacity</th>
                <td><strong><?= number_format((float)($log['recharge_capacity_kl'] ?? 0), 1) ?> kL / Day</strong></td>
            </tr>
            <tr>
                <th>Static Water Table Depth</th>
                <td><?= $log['water_table_depth_ft'] ? number_format((float)$log['water_table_depth_ft'], 1) . ' Ft Below Ground Level (BGL)' : 'Unrecorded' ?></td>
            </tr>
            <tr>
                <th>Filtration Media Grade</th>
                <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['filtration_media_status'] ?? 'clean_gravel_sand'))) ?></td>
            </tr>
            <tr>
                <th>Desilting Silt Traps</th>
                <td>
                    <?= !empty($log['desilting_chambers_cleared']) ? '<span class="badge badge-success">DESILTED & CLEARED</span>' : '<span class="badge badge-danger">ATTENTION REQUIRED</span>' ?>
                </td>
            </tr>
            <tr>
                <th>Monsoon Inflow Readiness</th>
                <td>
                    <span class="badge badge-success"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['monsoon_readiness_score'] ?? 'optimal_ready'))) ?></span>
                </td>
            </tr>
            <tr>
                <th>UP Groundwater Dept NOC</th>
                <td>
                    <span class="badge badge-info"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['up_groundwater_noc_status'] ?? 'compliant_valid'))) ?></span>
                </td>
            </tr>
            <?php if (!empty($log['maintenance_actions_done'])): ?>
            <tr>
                <th>Maintenance Completed</th>
                <td><?= htmlspecialchars($log['maintenance_actions_done']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Environmental Officer</th>
                <td><?= htmlspecialchars($log['environmental_officer_name'] ?: 'Township Environmental Engineer') ?> <?= !empty($log['officer_contact']) ? '(' . htmlspecialchars($log['officer_contact']) . ')' : '' ?></td>
            </tr>
            <?php if (!empty($log['remarks'])): ?>
            <tr>
                <th>Engineering Remarks</th>
                <td><?= nl2br(htmlspecialchars($log['remarks'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <div style="background: #f0fdf4; border: 1px dashed #86efac; padding: 12px; margin-top: 15px; font-size: 11px; color: #166534;">
            <strong>Environmental Sustainability Seal:</strong> This certifies that Rainwater Recharge Shaft <strong><?= htmlspecialchars($log['pit_identifier'] ?? 'Pit-1') ?></strong> conforms to UP Groundwater Management Rules and RERA green infrastructure provisions. Runoff water is de-silted and filtered through multi-grade gravel chambers before artificial aquifer recharge.
        </div>

        <div class="footer-signatures">
            <div class="sig-block">
                ENVIRONMENTAL OFFICER<br>
                <span>Water Resource Cell</span>
            </div>
            <div class="sig-block">
                CIVIL SITE ENGINEER<br>
                <span>Infrastructure Operations</span>
            </div>
            <div class="sig-block">
                CHIEF OPERATING OFFICER<br>
                <span>Township Development Board</span>
            </div>
        </div>
    </div>
</body>
</html>
