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
    <title>Commission Reconciliation â€” APS Dream Home</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/admin/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
</head>
<body>
<div class="aps-cp-card" class="style-33072">
    <div class="aps-cp-card-header">
        <h3><i class="fas fa-balance-scale"></i> Commission Reconciliation</h3>
        <span class="style-20203">
            <?php echo strtoupper($health); ?>
        </span>
    </div>
    <div class="aps-cp-card-body">
        <!-- Stats Row -->
        <div class="style-81558">
            <div class="aps-cp-stat">
                <div class="aps-cp-stat-value"><?php echo number_format($reconciliation['ledger_total'] ?? 0); ?></div>
                <div class="aps-cp-stat-label">Ledger Entries</div>
            </div>
            <div class="aps-cp-stat" class="style-14561">
                <div class="aps-cp-stat-value"><?php echo $summary['critical_issues'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Critical Issues</div>
            </div>
            <div class="aps-cp-stat" class="style-42923">
                <div class="aps-cp-stat-value"><?php echo $summary['warnings'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Warnings</div>
            </div>
        </div>

        <?php if (!empty($reconciliation['orphaned_ledger_no_booking'])): ?>
        <div class="style-46748">
            <h4><i class="fas fa-link" class="style-85206"></i> Orphaned Ledger Entries (<?php echo count($reconciliation['orphaned_ledger_no_booking']); ?>)</h4>
            <p class="style-73315">Commission entries referencing bookings that no longer exist.</p>
            <div class="table-responsive"><table class="style-14556">
                <thead><tr class="style-64307">
                    <th class="style-91688">Ledger ID</th>
                    <th class="style-91688">Booking</th>
                    <th class="style-91688">User</th>
                    <th class="style-91688">Type</th>
                    <th class="style-35252">Amount</th>
                    <th class="style-91688">Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($reconciliation['orphaned_ledger_no_booking'] as $ol): ?>
                <tr class="style-69418">
                    <td class="style-23927"><?php echo $ol['id']; ?></td>
                    <td class="style-23927"><?php echo $ol['booking_id']; ?></td>
                    <td class="style-23927"><?php echo $ol['beneficiary_user_id']; ?></td>
                    <td class="style-23927"><?php echo $ol['commission_type']; ?></td>
                    <td class="style-35252">â‚¹<?php echo number_format((float)$ol['amount'], 2); ?></td>
                    <td class="style-23927"><span class="badge badge-<?php echo $ol['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo $ol['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($reconciliation['missing_beneficiary'])): ?>
        <div class="style-46748">
            <h4><i class="fas fa-user-slash" class="style-85206"></i> Missing Beneficiaries (<?php echo count($reconciliation['missing_beneficiary']); ?>)</h4>
            <p class="style-73315">Commission entries pointing to users that no longer exist.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($reconciliation['orphaned_ledger_no_booking']) && empty($reconciliation['missing_beneficiary']) && empty($reconciliation['negative_entries'])): ?>
        <div class="style-15711">
            <i class="fas fa-check-circle" class="style-60715"></i>
            <h3>All Clear!</h3>
            <p>No discrepancies found across commission tables.</p>
        </div>
        <?php endif; ?>

        <div class="style-1082">
            Last reconciled: <?php echo $reconciliation['timestamp'] ?? 'Never'; ?>
        </div>
    </div>
</div>
</body>
</html>
