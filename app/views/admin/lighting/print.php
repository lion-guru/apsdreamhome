<?php
$log = $log ?? [];
$uptime = (float)($log['uptime_percent'] ?? 100.0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Street Lighting Uptime Certificate - #<?= (int)($log['id'] ?? 0) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; background: #fff; }
        .cert-container { border: 3px double #cbd5e1; padding: 35px; position: relative; max-width: 820px; margin: 0 auto; min-height: 980px; box-sizing: border-box; }
        .watermark { position: absolute; top: 40%; left: 15%; font-size: 55px; color: rgba(245, 158, 11, 0.05); transform: rotate(-30deg); font-weight: 800; pointer-events: none; z-index: 0; text-transform: uppercase; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 24px; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .header h3 { margin: 5px 0; font-size: 14px; color: #d97706; font-weight: 600; text-transform: uppercase; }
        .header p { margin: 0; font-size: 11px; color: #64748b; }
        .cert-title { text-align: center; margin: 25px 0; }
        .cert-title h2 { margin: 0; font-size: 17px; background: #fef3c7; color: #92400e; display: inline-block; padding: 6px 20px; border-radius: 4px; border: 1px solid #fde68a; letter-spacing: 0.5px; }
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .grid-table th, .grid-table td { border: 1px solid #e2e8f0; padding: 9px 12px; text-align: left; }
        .grid-table th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; width: 35%; }
        .grid-table td { color: #0f172a; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .footer-signatures { margin-top: 60px; display: flex; justify-content: space-between; text-align: center; }
        .sig-block { width: 220px; border-top: 1px solid #475569; padding-top: 6px; font-size: 11px; font-weight: bold; }
        @media print { .no-print { display: none; } body { padding: 0; } .cert-container { border: 2px solid #000; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; font-size: 14px; background: #d97706; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Print Street Lighting Certificate
        </button>
    </div>

    <div class="cert-container">
        <div class="watermark">APS DREAM HOME LIGHTING UPTIME</div>

        <div class="header">
            <h1>APS DREAM HOME PRIVATE LIMITED</h1>
            <h3>Township Electrical Infrastructure & Street Illumination Wing</h3>
            <p>Corporate Office: Civil Lines, Gorakhpur, Uttar Pradesh | ISO 9001:2015 Real Estate Township Operations</p>
        </div>

        <div class="cert-title">
            <h2>OFFICIAL STREET LIGHTING & FEEDER PILLAR AUDIT CERTIFICATE</h2>
        </div>

        <table class="grid-table">
            <tr>
                <th>Certificate Ref ID</th>
                <td><strong>APS-LIGHT-<?= str_pad((string)$log['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
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
                <th>Feeder Pillar Identifier</th>
                <td><strong><?= htmlspecialchars($log['feeder_pillar_code'] ?? 'FP-1') ?></strong></td>
            </tr>
            <tr>
                <th>Timer Control Mode</th>
                <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['timer_mode'] ?? 'astronomical_timer'))) ?></td>
            </tr>
            <tr>
                <th>Operational Timings</th>
                <td>ON: <?= $log['on_time'] ? date('h:i A', strtotime($log['on_time'])) : '18:15' ?> | OFF: <?= $log['off_time'] ? date('h:i A', strtotime($log['off_time'])) : '05:45' ?></td>
            </tr>
            <tr>
                <th>Poles Illumination Audit</th>
                <td>
                    <strong><?= (int)$log['poles_functional'] ?> Functional</strong> / <?= (int)$log['total_poles_connected'] ?> Total Connected Poles
                    <?php if ((int)$log['poles_faulty'] > 0): ?>
                        <span class="badge badge-danger"><?= (int)$log['poles_faulty'] ?> Faulty</span>
                    <?php else: ?>
                        <span class="badge badge-success">Zero Blackout</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Luminance Uptime Percentage</th>
                <td>
                    <span class="badge <?= ($uptime >= 95) ? 'badge-success' : (($uptime >= 80) ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 13px;">
                        <?= number_format($uptime, 2) ?>% Uptime
                    </span>
                </td>
            </tr>
            <tr>
                <th>Phase Balance (R-Y-B)</th>
                <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['phase_balance_status'] ?? 'balanced_ryb'))) ?></td>
            </tr>
            <tr>
                <th>Armoured Cable & Joint Health</th>
                <td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['cable_health'] ?? 'healthy_underground'))) ?></td>
            </tr>
            <?php if (!empty($log['faulty_pole_numbers'])): ?>
            <tr>
                <th>Faulty Poles / Replacement Tag</th>
                <td style="color: #b91c1c;"><strong><?= htmlspecialchars($log['faulty_pole_numbers']) ?></strong></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Duty Electrician / Contact</th>
                <td><?= htmlspecialchars($log['electrician_name'] ?: 'Duty Staff') ?> <?= !empty($log['electrician_phone']) ? '(' . htmlspecialchars($log['electrician_phone']) . ')' : '' ?></td>
            </tr>
            <?php if (!empty($log['remarks'])): ?>
            <tr>
                <th>Engineering Remarks</th>
                <td><?= nl2br(htmlspecialchars($log['remarks'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 12px; margin-top: 15px; font-size: 11px; color: #475569;">
            <strong>Township Electrical Safety Declaration:</strong> This certifies that Feeder Pillar <strong><?= htmlspecialchars($log['feeder_pillar_code'] ?? 'FP-1') ?></strong> has been physically inspected by the electrical team. Earth resistance (earthing pit) and Miniature Circuit Breakers (MCB) are within Bureau of Indian Standards (BIS / CEA) safety norms.
        </div>

        <div class="footer-signatures">
            <div class="sig-block">
                DUTY ELECTRICIAN<br>
                <span>APS Township Electrical Team</span>
            </div>
            <div class="sig-block">
                ELECTRICAL SUPERVISOR<br>
                <span>Site Engineering Cell</span>
            </div>
            <div class="sig-block">
                PROJECT DIRECTOR<br>
                <span>Township Infrastructure Directorate</span>
            </div>
        </div>
    </div>
</body>
</html>
