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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h3><i class="fas fa-balance-scale"></i> Commission Reconciliation</h3>
        <span >
            <?php echo strtoupper($health); ?>
        </span>
    </div>
    <div class="aps-cp-card-body">
        <!-- Stats Row -->
        <div >
            <div class="aps-cp-stat">
                <div class="aps-cp-stat-value"><?php echo number_format($reconciliation['ledger_total'] ?? 0); ?></div>
                <div class="aps-cp-stat-label">Ledger Entries</div>
            </div>
            <div class="aps-cp-stat">
                <div class="aps-cp-stat-value"><?php echo $summary['critical_issues'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Critical Issues</div>
            </div>
            <div class="aps-cp-stat">
                <div class="aps-cp-stat-value"><?php echo $summary['warnings'] ?? 0; ?></div>
                <div class="aps-cp-stat-label">Warnings</div>
            </div>
        </div>

        <?php if (!empty($reconciliation['orphaned_ledger_no_booking'])): ?>
        <div >
            <h4><i class="fas fa-link"></i> Orphaned Ledger Entries (<?php echo count($reconciliation['orphaned_ledger_no_booking']); ?>)</h4>
            <p >Commission entries referencing bookings that no longer exist.</p>
            <div class="table-responsive"><table >
                <thead><tr >
                    <th >Ledger ID</th>
                    <th >Booking</th>
                    <th >User</th>
                    <th >Type</th>
                    <th >Amount</th>
                    <th >Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($reconciliation['orphaned_ledger_no_booking'] as $ol): ?>
                <tr >
                    <td ><?php echo e($ol['id']); ?></td>
                    <td ><?php echo e($ol['booking_id']); ?></td>
                    <td ><?php echo e($ol['beneficiary_user_id']); ?></td>
                    <td ><?php echo e($ol['commission_type']); ?></td>
                    <td >₹<?php echo number_format((float)$ol['amount'], 2); ?></td>
                    <td ><span class="badge badge-<?php echo $ol['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo e($ol['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($reconciliation['missing_beneficiary'])): ?>
        <div >
            <h4><i class="fas fa-user-slash"></i> Missing Beneficiaries (<?php echo count($reconciliation['missing_beneficiary']); ?>)</h4>
            <p >Commission entries pointing to users that no longer exist.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($reconciliation['orphaned_ledger_no_booking']) && empty($reconciliation['missing_beneficiary']) && empty($reconciliation['negative_entries'])): ?>
        <div >
            <i class="fas fa-check-circle"></i>
            <h3>All Clear!</h3>
            <p>No discrepancies found across commission tables.</p>
        </div>
        <?php endif; ?>

        <div >
            Last reconciled: <?php echo $reconciliation['timestamp'] ?? 'Never'; ?>
        </div>
    </div>
</div>
</body>
</html>
