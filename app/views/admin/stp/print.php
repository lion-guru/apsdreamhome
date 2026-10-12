<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home – STP Effluent Analysis Certificate</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #1a1a1a; background: #fff; }
        .certificate { width: 100%; min-height: 267mm; border: 3px double #1a5276; padding: 10mm; position: relative; }
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80pt; color: rgba(26,82,118,0.05); font-weight: 900; letter-spacing: 2px;
            pointer-events: none; white-space: nowrap; z-index: 0;
        }
        .content { position: relative; z-index: 1; }
        .header { text-align: center; border-bottom: 2px solid #1a5276; padding-bottom: 6mm; margin-bottom: 5mm; }
        .logo-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3mm; }
        .logo-block { font-size: 22pt; font-weight: 900; color: #1a5276; letter-spacing: 1px; }
        .logo-sub { font-size: 9pt; color: #666; font-weight: normal; }
        .cert-title { font-size: 15pt; font-weight: 700; color: #1a5276; text-transform: uppercase; letter-spacing: 1px; }
        .cert-subtitle { font-size: 10pt; color: #444; margin-top: 2mm; }
        .badge-row { display: flex; justify-content: center; gap: 6mm; margin-top: 3mm; flex-wrap: wrap; }
        .badge { border: 1px solid #1a5276; border-radius: 3px; padding: 1mm 4mm; font-size: 8pt; color: #1a5276; font-weight: 700; text-transform: uppercase; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 3mm; margin: 5mm 0; }
        .meta-item label { font-size: 8pt; color: #666; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-item span { font-size: 10pt; font-weight: 700; color: #1a1a1a; }
        .section-title { font-size: 10pt; font-weight: 700; color: #1a5276; text-transform: uppercase; letter-spacing: 0.5px; border-left: 3px solid #1a5276; padding-left: 3mm; margin: 5mm 0 3mm; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 4mm; font-size: 9.5pt; }
        .data-table th { background: #1a5276; color: #fff; padding: 2mm 3mm; text-align: left; font-size: 8.5pt; text-transform: uppercase; }
        .data-table td { padding: 2mm 3mm; border-bottom: 1px solid #e0e0e0; }
        .data-table tr:nth-child(even) td { background: #f5f9fc; }
        .compliance-badge { display: inline-block; padding: 1mm 4mm; border-radius: 3px; font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-compliant { background: #d5f5e3; color: #0a6b35; border: 1px solid #27ae60; }
        .status-minor { background: #fef9e7; color: #7d6608; border: 1px solid #f39c12; }
        .status-critical { background: #fde8e8; color: #7b1919; border: 1px solid #e74c3c; }
        .status-lab { background: #eaf4fb; color: #154360; border: 1px solid #2980b9; }
        .limits-note { background: #eaf4fb; border: 1px solid #aed6f1; border-radius: 4px; padding: 3mm; font-size: 8pt; color: #1a4a6e; margin: 3mm 0; }
        .limits-note strong { color: #1a5276; }
        .seal-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 8mm; border-top: 1px dashed #aaa; padding-top: 5mm; }
        .seal-block { text-align: center; width: 45mm; }
        .seal-circle { width: 30mm; height: 30mm; border-radius: 50%; border: 2px solid #1a5276; margin: 0 auto 2mm; display: flex; align-items: center; justify-content: center; font-size: 7pt; font-weight: 700; color: #1a5276; text-align: center; line-height: 1.2; text-transform: uppercase; }
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

$complianceClass = 'status-compliant';
$complianceTxt   = 'Fully UPPCB Compliant';
switch ($log['uppcb_compliance_status'] ?? '') {
    case 'minor_deviation':   $complianceClass = 'status-minor';    $complianceTxt = 'Minor Deviation';    break;
    case 'critical_violation':$complianceClass = 'status-critical'; $complianceTxt = 'Critical Violation'; break;
    case 'sample_sent_lab':   $complianceClass = 'status-lab';      $complianceTxt = 'Sample Sent to Lab'; break;
}

$inletBod  = isset($log['inlet_bod_mg_l'])  ? number_format((float)$log['inlet_bod_mg_l'], 1)  : 'N/T';
$outletBod = isset($log['outlet_bod_mg_l']) ? number_format((float)$log['outlet_bod_mg_l'], 1) : 'N/T';
$inletCod  = isset($log['inlet_cod_mg_l'])  ? number_format((float)$log['inlet_cod_mg_l'], 1)  : 'N/T';
$outletCod = isset($log['outlet_cod_mg_l']) ? number_format((float)$log['outlet_cod_mg_l'], 1) : 'N/T';
$outletTss = isset($log['outlet_tss_mg_l']) ? number_format((float)$log['outlet_tss_mg_l'], 1) : 'N/T';
$outletPh  = isset($log['outlet_ph'])       ? number_format((float)$log['outlet_ph'], 1)       : 'N/T';
$chlorine  = isset($log['chlorine_dosing_ppm']) ? number_format((float)$log['chlorine_dosing_ppm'], 2) . ' ppm' : 'N/T';

$bodEff = 0.0;
if (isset($log['inlet_bod_mg_l'], $log['outlet_bod_mg_l']) && (float)$log['inlet_bod_mg_l'] > 0) {
    $bodEff = round((((float)$log['inlet_bod_mg_l'] - (float)$log['outlet_bod_mg_l']) / (float)$log['inlet_bod_mg_l']) * 100, 1);
}

$reuseLabelMap = [
    'gardening_horticulture'        => 'Township Gardening & Horticulture',
    'toilet_dual_flush'             => 'Resident Toilet Dual-Flush',
    'road_washing_dust_suppression' => 'Road Washing & Dust Suppression',
    'groundwater_recharge'          => 'Groundwater Recharge Pit',
    'overspill_drain'               => 'Controlled Drain Outfall',
];
$reuseLabel = $reuseLabelMap[$log['water_reuse_purpose'] ?? ''] ?? ucwords(str_replace('_', ' ', $log['water_reuse_purpose'] ?? 'N/A'));
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
                    <strong>Cert. No.:</strong> APS-STP-<?= date('Y') ?>-<?= str_pad($log['id'] ?? 0, 5, '0', STR_PAD_LEFT) ?><br>
                    <strong>Issue Date:</strong> <?= date('d M Y') ?><br>
                    <strong>Log Date:</strong> <?= date('d M Y', strtotime($log['log_date'] ?? 'now')) ?>
                </div>
            </div>
            <div class="cert-title">Sewage Treatment Plant (STP) Effluent Analysis Certificate</div>
            <div class="cert-subtitle">UPPCB Effluent Discharge Standard Compliance — Colony Wastewater Quality Report</div>
            <div class="badge-row">
                <span class="badge">UPPCB Schedule-VI Discharge</span>
                <span class="badge">MoEFCC Notification 2016</span>
                <span class="badge">UP Pollution Control Board</span>
                <span class="badge">SBM Urban Reuse Compliant</span>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="meta-grid">
            <div class="meta-item"><label>Township Colony</label><span><?= $cname ?></span></div>
            <div class="meta-item"><label>Address</label><span><?= $caddr ?></span></div>
            <div class="meta-item"><label>STP Plant Code / Unit</label><span><?= htmlspecialchars($log['stp_plant_code'] ?? 'N/A') ?></span></div>
            <div class="meta-item"><label>Raw Sewage Inflow</label><span><?= number_format((float)($log['raw_sewage_inflow_kl'] ?? 0), 1) ?> kL</span></div>
            <div class="meta-item"><label>Treated Water Output</label><span><?= number_format((float)($log['treated_water_output_kl'] ?? 0), 1) ?> kL</span></div>
            <div class="meta-item"><label>Aeration Blower Runtime</label><span><?= number_format((float)($log['aeration_blower_hours'] ?? 0), 1) ?> hrs</span></div>
        </div>

        <!-- UPPCB Compliance -->
        <div style="text-align:center; margin: 4mm 0;">
            <span class="compliance-badge <?= $complianceClass ?>"><?= $complianceTxt ?></span>
            &nbsp;&nbsp;
            <?php if ($bodEff > 0): ?>
            <span style="font-size:9pt; color:#1a5276; font-weight:700;">BOD Removal Efficiency: <?= $bodEff ?>%</span>
            <?php endif; ?>
        </div>

        <!-- Lab Analysis Table -->
        <div class="section-title">📊 Effluent Lab Analysis — UPPCB Discharge Parameters</div>
        <div class="limits-note">
            <strong>UPPCB Schedule-VI Statutory Limits:</strong> BOD ≤ 10 mg/L &nbsp;|&nbsp; COD ≤ 50 mg/L &nbsp;|&nbsp; TSS ≤ 20 mg/L &nbsp;|&nbsp; pH 6.5 – 8.5 &nbsp;|&nbsp; Chlorine ≤ 1.0 ppm
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Inlet (Raw Sewage)</th>
                    <th>Outlet (Treated)</th>
                    <th>Statutory Limit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Biochemical Oxygen Demand (BOD)</strong></td>
                    <td><?= $inletBod ?> mg/L</td>
                    <td><?= $outletBod ?> mg/L</td>
                    <td>≤ 10 mg/L</td>
                    <td><?= (float)($log['outlet_bod_mg_l'] ?? 0) <= 10.0 ? '✅ Pass' : '❌ Fail' ?></td>
                </tr>
                <tr>
                    <td><strong>Chemical Oxygen Demand (COD)</strong></td>
                    <td><?= $inletCod ?> mg/L</td>
                    <td><?= $outletCod ?> mg/L</td>
                    <td>≤ 50 mg/L</td>
                    <td><?= (float)($log['outlet_cod_mg_l'] ?? 0) <= 50.0 ? '✅ Pass' : '❌ Fail' ?></td>
                </tr>
                <tr>
                    <td><strong>Total Suspended Solids (TSS)</strong></td>
                    <td>— (Outlet only)</td>
                    <td><?= $outletTss ?> mg/L</td>
                    <td>≤ 20 mg/L</td>
                    <td><?= (float)($log['outlet_tss_mg_l'] ?? 0) <= 20.0 ? '✅ Pass' : '❌ Fail' ?></td>
                </tr>
                <tr>
                    <td><strong>pH Level</strong></td>
                    <td>— (Outlet only)</td>
                    <td><?= $outletPh ?></td>
                    <td>6.5 – 8.5</td>
                    <td><?php $ph = (float)($log['outlet_ph'] ?? 7.0); echo ($ph >= 6.5 && $ph <= 8.5) ? '✅ Pass' : '❌ Fail'; ?></td>
                </tr>
                <tr>
                    <td><strong>Residual Chlorine Dosing</strong></td>
                    <td colspan="2"><?= $chlorine ?></td>
                    <td>≤ 1.0 ppm</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>

        <!-- Operational Details -->
        <div class="section-title">🔧 Operational & Sludge Management Details</div>
        <table class="data-table">
            <tr>
                <th>Treated Water Reuse Purpose</th>
                <td colspan="3"><?= htmlspecialchars($reuseLabel) ?></td>
            </tr>
            <tr>
                <td style="width:35%;"><strong>Sludge Cake Disposed</strong></td>
                <td><?= number_format((float)($log['sludge_cake_disposed_kg'] ?? 0), 1) ?> kg</td>
                <td style="width:25%;"><strong>Operator</strong></td>
                <td><?= htmlspecialchars($log['operator_name'] ?? 'N/A') ?> | <?= htmlspecialchars($log['operator_phone'] ?? 'N/A') ?></td>
            </tr>
            <?php if (!empty($log['remarks'])): ?>
            <tr>
                <td><strong>Operator Remarks</strong></td>
                <td colspan="3"><?= htmlspecialchars($log['remarks']) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Seals -->
        <div class="seal-row">
            <div class="seal-block">
                <div class="seal-circle">STP<br>Operator<br>Seal</div>
                <p>STP Plant Operator</p>
                <strong><?= htmlspecialchars($log['operator_name'] ?? '') ?></strong>
            </div>
            <div style="text-align:center; font-size:9pt; color:#555; max-width:90mm;">
                <p>This certificate confirms the effluent quality parameters as tested on <strong><?= date('d M Y', strtotime($log['log_date'] ?? 'now')) ?></strong> from <strong><?= htmlspecialchars($log['stp_plant_code'] ?? '') ?></strong>.</p>
                <p style="margin-top:2mm;">Issued under authority of APS Dream Home Township Operations Division in accordance with UPPCB Schedule-VI discharge norms and MoEFCC Environment Protection Rules 1986.</p>
            </div>
            <div class="seal-block">
                <div class="seal-circle">APS<br>ETP<br>Manager</div>
                <p>Environment & Compliance Head</p>
                <strong>APS Dream Home</strong>
            </div>
        </div>

        <div class="footer-note">
            APS Dream Home — Township Operations Division &nbsp;|&nbsp; Gorakhpur, Uttar Pradesh &nbsp;|&nbsp;
            Cert No: APS-STP-<?= date('Y') ?>-<?= str_pad($log['id'] ?? 0, 5, '0', STR_PAD_LEFT) ?> &nbsp;|&nbsp;
            Generated: <?= date('d M Y H:i') ?> &nbsp;|&nbsp;
            This is a computer-generated document. Valid without physical signature under APS Digital Certification Policy.
        </div>
    </div>
</div>

<div class="no-print" style="text-align:center; margin:20px;">
    <button onclick="window.print()" style="padding:10px 30px; background:#1a5276; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer;">🖨️ Print Certificate</button>
    &nbsp;
    <a href="/admin/township-stp" style="padding:10px 20px; background:#6c757d; color:#fff; border-radius:6px; text-decoration:none; font-size:14px;">← Back to STP Desk</a>
</div>
</body>
</html>
