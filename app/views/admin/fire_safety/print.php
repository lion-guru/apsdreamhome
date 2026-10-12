<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home – Fire Hydrant NOC Inspection Certificate</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #1a1a1a; background: #fff; }
        .certificate { width: 100%; min-height: 267mm; border: 3px double #7b1a1a; padding: 10mm; position: relative; }
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80pt; color: rgba(123,26,26,0.05); font-weight: 900; letter-spacing: 2px;
            pointer-events: none; white-space: nowrap; z-index: 0;
        }
        .content { position: relative; z-index: 1; }
        .header { text-align: center; border-bottom: 2px solid #7b1a1a; padding-bottom: 6mm; margin-bottom: 5mm; }
        .logo-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3mm; }
        .logo-block { font-size: 22pt; font-weight: 900; color: #7b1a1a; letter-spacing: 1px; }
        .logo-sub { font-size: 9pt; color: #666; font-weight: normal; }
        .cert-title { font-size: 14pt; font-weight: 700; color: #7b1a1a; text-transform: uppercase; letter-spacing: 1px; }
        .cert-subtitle { font-size: 10pt; color: #444; margin-top: 2mm; }
        .badge-row { display: flex; justify-content: center; gap: 5mm; margin-top: 3mm; flex-wrap: wrap; }
        .badge { border: 1px solid #7b1a1a; border-radius: 3px; padding: 1mm 4mm; font-size: 8pt; color: #7b1a1a; font-weight: 700; text-transform: uppercase; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 3mm; margin: 4mm 0; }
        .meta-item label { font-size: 8pt; color: #666; display: block; text-transform: uppercase; }
        .meta-item span { font-size: 10pt; font-weight: 700; color: #1a1a1a; }
        .section-title { font-size: 10pt; font-weight: 700; color: #7b1a1a; text-transform: uppercase; letter-spacing: 0.5px; border-left: 3px solid #7b1a1a; padding-left: 3mm; margin: 5mm 0 3mm; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 4mm; font-size: 9.5pt; }
        .data-table th { background: #7b1a1a; color: #fff; padding: 2mm 3mm; text-align: left; font-size: 8.5pt; text-transform: uppercase; }
        .data-table td { padding: 2mm 3mm; border-bottom: 1px solid #e0e0e0; }
        .data-table tr:nth-child(even) td { background: #fdf5f5; }
        .status-badge { display: inline-block; padding: 1mm 4mm; border-radius: 3px; font-size: 9pt; font-weight: 700; }
        .status-ok     { background: #d5f5e3; color: #0a6b35; border: 1px solid #27ae60; }
        .status-warn   { background: #fef9e7; color: #7d6608; border: 1px solid #f39c12; }
        .status-danger { background: #fde8e8; color: #7b1919; border: 1px solid #e74c3c; }
        .status-info   { background: #eaf4fb; color: #154360; border: 1px solid #2980b9; }
        .pump-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4mm; margin: 3mm 0; }
        .pump-card { background: #fdf5f5; border: 1px solid #e8c0c0; border-radius: 6px; padding: 3mm; text-align: center; }
        .pump-card label { font-size: 7.5pt; color: #7b1a1a; text-transform: uppercase; display: block; font-weight: 700; margin-bottom: 1mm; }
        .pump-card span { font-size: 9pt; font-weight: 700; }
        .seal-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 8mm; border-top: 1px dashed #aaa; padding-top: 5mm; }
        .seal-block { text-align: center; width: 45mm; }
        .seal-circle { width: 30mm; height: 30mm; border-radius: 50%; border: 2px solid #7b1a1a; margin: 0 auto 2mm; display: flex; align-items: center; justify-content: center; font-size: 7pt; font-weight: 700; color: #7b1a1a; text-align: center; line-height: 1.2; text-transform: uppercase; }
        .seal-block p { font-size: 8pt; color: #444; }
        .seal-block strong { font-size: 8.5pt; display: block; margin-top: 1mm; }
        .footer-note { text-align: center; font-size: 7.5pt; color: #777; margin-top: 4mm; border-top: 1px solid #e0e0e0; padding-top: 3mm; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<?php
$log   = $log ?? [];
$cname = htmlspecialchars($log['colony_name'] ?? 'N/A');
$caddr = htmlspecialchars($log['colony_address'] ?? 'Gorakhpur, Uttar Pradesh');

$nocClass = 'status-ok';
$nocLabel = 'Valid & Compliant';
switch ($log['up_fire_noc_status'] ?? '') {
    case 'renewal_due_30d':  $nocClass = 'status-warn';   $nocLabel = 'Renewal Due in 30 Days'; break;
    case 'renewal_applied':  $nocClass = 'status-info';   $nocLabel = 'Renewal Applied';         break;
    case 'provisional_noc':  $nocClass = 'status-info';   $nocLabel = 'Provisional NOC Active';  break;
    case 'expired':          $nocClass = 'status-danger'; $nocLabel = 'NOC Expired — Action Req'; break;
}

$valvesPct = 100.0;
if ((int)($log['hydrant_valves_tested'] ?? 0) > 0) {
    $valvesPct = round(((int)($log['hydrant_valves_healthy'] ?? 0) / (int)$log['hydrant_valves_tested']) * 100, 1);
}

$pumpStatusLabel = function(string $key, string $val, array $map): string {
    return $map[$val] ?? ucwords(str_replace('_', ' ', $val));
};

$jpMap = ['auto_standby' => 'Auto Standby ✅', 'running_pressurizing' => 'Running (Pressurizing)', 'manual_mode' => 'Manual Mode', 'fault_tripped' => 'Fault / Tripped ❌'];
$epMap = ['operational_ready' => 'Operational Ready ✅', 'service_due' => 'Service Due', 'phase_trip' => 'Phase Trip ⚠️', 'breakdown' => 'Breakdown ❌'];
$dpMap = ['operational_ready' => 'Operational Ready ✅', 'battery_weak' => 'Battery Weak', 'fuel_low' => 'Fuel Low', 'cranking_failure' => 'Cranking Failure ❌'];
?>
<div class="certificate">
    <div class="watermark">APS</div>
    <div class="content">
        <!-- Header -->
        <div class="header">
            <div class="logo-row">
                <div class="logo-block">
                    APS Dream Home<br>
                    <span class="logo-sub">Real Estate & Township Development, Gorakhpur</span>
                </div>
                <div style="text-align:right; font-size:8pt; color:#555;">
                    <strong>Cert. No.:</strong> APS-FIRE-<?= date('Y') ?>-<?= str_pad($log['id'] ?? 0, 5, '0', STR_PAD_LEFT) ?><br>
                    <strong>Issue Date:</strong> <?= date('d M Y') ?><br>
                    <strong>Inspection Date:</strong> <?= date('d M Y', strtotime($log['inspection_date'] ?? 'now')) ?>
                </div>
            </div>
            <div class="cert-title">🔥 Fire Hydrant Network & UP Fire Safety NOC Certificate</div>
            <div class="cert-subtitle">Annual Inspection of Fire Fighting Infrastructure — Pump Readiness, Hydrant Valves & NOC Compliance</div>
            <div class="badge-row">
                <span class="badge">UP Fire Prevention Act</span>
                <span class="badge">NBC 2016 Part IV</span>
                <span class="badge">UP Fire NOC Compliance</span>
                <span class="badge">IS 3844 Ring Main</span>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="meta-grid">
            <div class="meta-item"><label>Township Colony</label><span><?= $cname ?></span></div>
            <div class="meta-item"><label>Address</label><span><?= $caddr ?></span></div>
            <div class="meta-item"><label>Fire Zone / Ring Main</label><span><?= htmlspecialchars($log['fire_zone_identifier'] ?? 'N/A') ?></span></div>
            <div class="meta-item"><label>Static Line Pressure</label><span><?= number_format((float)($log['static_line_pressure_bar'] ?? 0), 1) ?> Bar</span></div>
            <div class="meta-item"><label>Fire Water Storage Reserve</label><span><?= number_format((float)($log['fire_water_storage_kl'] ?? 0), 1) ?> kL</span></div>
            <div class="meta-item"><label>UP Fire NOC Status</label><span><span class="status-badge <?= $nocClass ?>"><?= $nocLabel ?></span></span></div>
        </div>

        <!-- NOC Details -->
        <?php if (!empty($log['noc_certificate_number'])): ?>
        <div style="background:#fdf5f5; border:1px solid #e8c0c0; border-radius:4px; padding:3mm; margin:3mm 0; font-size:9pt;">
            <strong>NOC Certificate No.:</strong> <?= htmlspecialchars($log['noc_certificate_number']) ?>
            <?php if (!empty($log['noc_expiry_date'])): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Expiry Date:</strong> <?= date('d M Y', strtotime($log['noc_expiry_date'])) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Pump Status -->
        <div class="section-title">🚒 Fire Pump & Pressure System Status</div>
        <div class="pump-grid">
            <div class="pump-card">
                <label>Jockey Pump (Pressure Maintainer)</label>
                <span><?= $jpMap[$log['jockey_pump_status'] ?? ''] ?? ucwords(str_replace('_', ' ', $log['jockey_pump_status'] ?? 'N/A')) ?></span>
            </div>
            <div class="pump-card">
                <label>Main Electric Pump (Primary)</label>
                <span><?= $epMap[$log['main_electric_pump_status'] ?? ''] ?? ucwords(str_replace('_', ' ', $log['main_electric_pump_status'] ?? 'N/A')) ?></span>
            </div>
            <div class="pump-card">
                <label>Diesel Engine Backup (Standby)</label>
                <span><?= $dpMap[$log['diesel_engine_backup_status'] ?? ''] ?? ucwords(str_replace('_', ' ', $log['diesel_engine_backup_status'] ?? 'N/A')) ?></span>
            </div>
        </div>

        <!-- Hydrant Network Audit -->
        <div class="section-title">🧯 Hydrant Network & Fire Equipment Audit</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Tested / Qty</th>
                    <th>Healthy / OK</th>
                    <th>Health %</th>
                    <th>Condition</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Hydrant Landing Valves</strong></td>
                    <td><?= (int)($log['hydrant_valves_tested'] ?? 0) ?></td>
                    <td><?= (int)($log['hydrant_valves_healthy'] ?? 0) ?></td>
                    <td><?= $valvesPct ?>%</td>
                    <td><?= $valvesPct >= 90 ? '✅ Good' : ($valvesPct >= 70 ? '⚠️ Fair' : '❌ Poor') ?></td>
                </tr>
                <tr>
                    <td><strong>Hose Boxes &amp; Reels</strong></td>
                    <td colspan="2"><?= (int)($log['hose_boxes_equipped'] ?? 0) ?> Nos. Equipped</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
                <tr>
                    <td><strong>Portable Extinguishers</strong></td>
                    <td colspan="2"><?= (int)($log['extinguishers_checked'] ?? 0) ?> Nos. Checked</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
                <tr>
                    <td><strong>Fire Drill Conducted</strong></td>
                    <td colspan="2"><?= !empty($log['fire_drill_conducted']) && $log['fire_drill_conducted'] ? 'Yes — Drill Completed ✅' : 'No' ?></td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>

        <!-- Defects & Officer -->
        <table class="data-table">
            <tr>
                <th style="width:30%;">Fire Safety Officer</th>
                <td><?= htmlspecialchars($log['fire_officer_name'] ?? 'N/A') ?> | <?= htmlspecialchars($log['fire_officer_contact'] ?? 'N/A') ?></td>
            </tr>
            <?php if (!empty($log['defects_observed'])): ?>
            <tr>
                <th>Defects Observed</th>
                <td style="color:#c0392b;"><?= htmlspecialchars($log['defects_observed']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($log['remarks'])): ?>
            <tr>
                <th>Inspection Remarks</th>
                <td><?= htmlspecialchars($log['remarks']) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Seals -->
        <div class="seal-row">
            <div class="seal-block">
                <div class="seal-circle">Fire<br>Safety<br>Officer</div>
                <p>Township Fire Safety Officer</p>
                <strong><?= htmlspecialchars($log['fire_officer_name'] ?? '') ?></strong>
            </div>
            <div style="text-align:center; font-size:9pt; color:#555; max-width:90mm;">
                <p>This certificate confirms the fire fighting infrastructure inspection carried out on <strong><?= date('d M Y', strtotime($log['inspection_date'] ?? 'now')) ?></strong> at <strong><?= htmlspecialchars($log['fire_zone_identifier'] ?? '') ?></strong>, <?= $cname ?>.</p>
                <p style="margin-top:2mm;">Issued under APS Dream Home Township Safety Division in compliance with UP Fire Prevention Act, National Building Code Part IV – Fire & Life Safety, and IS 3844 Ring Main System standards.</p>
            </div>
            <div class="seal-block">
                <div class="seal-circle">APS<br>Safety<br>Mgr</div>
                <p>Township Safety Manager</p>
                <strong>APS Dream Home</strong>
            </div>
        </div>

        <div class="footer-note">
            APS Dream Home — Township Safety Division &nbsp;|&nbsp; Gorakhpur, Uttar Pradesh &nbsp;|&nbsp;
            Cert No: APS-FIRE-<?= date('Y') ?>-<?= str_pad($log['id'] ?? 0, 5, '0', STR_PAD_LEFT) ?> &nbsp;|&nbsp;
            Generated: <?= date('d M Y H:i') ?> &nbsp;|&nbsp;
            This is a computer-generated certificate. Valid without physical signature under APS Digital Certification Policy.
        </div>
    </div>
</div>

<div class="no-print" style="text-align:center; margin:20px;">
    <button onclick="window.print()" style="padding:10px 30px; background:#7b1a1a; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer;">🖨️ Print Certificate</button>
    &nbsp;
    <a href="/admin/township-fire-safety" style="padding:10px 20px; background:#6c757d; color:#fff; border-radius:6px; text-decoration:none; font-size:14px;">← Back to Fire Safety Desk</a>
</div>
</body>
</html>
