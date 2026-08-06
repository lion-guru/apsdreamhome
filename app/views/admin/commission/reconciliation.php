<?php
/**
 * Commission Reconciliation Dashboard
 * Shows daily audit results for mlm_commission_ledger (single source of truth).
 */
$reconciliation = $data ?? [];
$summary = $reconciliation['summary'] ?? [];
$health = $summary['health'] ?? 'unknown';
$healthColors = ['healthy' => '#10b981', 'warning' => '#f59e0b', 'critical' => '#ef4444'];
$healthColor = $healthColors[$health] ?? '#6b7280';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Reconciliation — APS Dream Home</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/admin/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
</head>
<body>
<div class="aps-cp-card" style="margin: 20px;">
    <div class="aps-cp-card-header">
        <h3><i class="fas fa-balance-scale"></i> Commission Reconciliation</h3>
        <span style="padding:4px 12px;border-radius:12px;background:<?php echo $healthColor; ?>;color:white;font-weight:600;">
            <?php echo strtoupper($health); ?>
        </span>
    </div>
    <div class="aps-cp-card-body">
        <!-- Stats Row -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
            <div class="aps-cp-stat">
                <div class="aps-cp-stat-value"><?php echo number_format($reconciliation['ledger_total'] ?? 0); ?></div>
                <div class="aps-cp-stat-label">Ledger Entries</div>
            </div>
            <div class="aps-cp-stat" style="border-left:3px solid <?php echo ($summary['critical_issues'] ?? 0) > 0 ? '#ef4444' : '#10b981'; ?>">
                <div class="aps-cp-stat-value"><?php echo $summary['critical_issues'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Critical Issues</div>
            </div>
            <div class="aps-cp-stat" style="border-left:3px solid <?php echo ($summary['warnings'] ?? 0) > 0 ? '#f59e0b' : '#10b981'; ?>">
                <div class="aps-cp-stat-value"><?php echo $summary['warnings'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Warnings</div>
            </div>
        </div>

        <?php if (!empty($reconciliation['orphaned_ledger_no_booking'])): ?>
        <div style="margin-bottom:24px;">
            <h4><i class="fas fa-link" style="color:#ef4444;"></i> Orphaned Ledger Entries (<?php echo count($reconciliation['orphaned_ledger_no_booking']); ?>)</h4>
            <p style="color:#64748b;font-size:0.9em;">Commission entries referencing bookings that no longer exist.</p>
            <div class="table-responsive"><table style="width:100%;border-collapse:collapse;font-size:0.85em;">
                <thead><tr style="background:#fef2f2;">
                    <th style="padding:8px;text-align:left;">Ledger ID</th>
                    <th style="padding:8px;text-align:left;">Booking</th>
                    <th style="padding:8px;text-align:left;">User</th>
                    <th style="padding:8px;text-align:left;">Type</th>
                    <th style="padding:8px;text-align:right;">Amount</th>
                    <th style="padding:8px;text-align:left;">Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($reconciliation['orphaned_ledger_no_booking'] as $ol): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:8px;"><?php echo $ol['id']; ?></td>
                    <td style="padding:8px;"><?php echo $ol['booking_id']; ?></td>
                    <td style="padding:8px;"><?php echo $ol['beneficiary_user_id']; ?></td>
                    <td style="padding:8px;"><?php echo $ol['commission_type']; ?></td>
                    <td style="padding:8px;text-align:right;">₹<?php echo number_format((float)$ol['amount'], 2); ?></td>
                    <td style="padding:8px;"><span class="badge badge-<?php echo $ol['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo $ol['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($reconciliation['missing_beneficiary'])): ?>
        <div style="margin-bottom:24px;">
            <h4><i class="fas fa-user-slash" style="color:#ef4444;"></i> Missing Beneficiaries (<?php echo count($reconciliation['missing_beneficiary']); ?>)</h4>
            <p style="color:#64748b;font-size:0.9em;">Commission entries pointing to users that no longer exist.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($reconciliation['orphaned_ledger_no_booking']) && empty($reconciliation['missing_beneficiary']) && empty($reconciliation['negative_entries'])): ?>
        <div style="text-align:center;padding:40px;color:#10b981;">
            <i class="fas fa-check-circle" style="font-size:48px;margin-bottom:12px;"></i>
            <h3>All Clear!</h3>
            <p>No discrepancies found across commission tables.</p>
        </div>
        <?php endif; ?>

        <div style="margin-top:16px;font-size:0.8em;color:#94a3b8;">
            Last reconciled: <?php echo $reconciliation['timestamp'] ?? 'Never'; ?>
        </div>
    </div>
</div>
</body>
</html>
