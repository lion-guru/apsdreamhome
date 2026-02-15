<?php
// Associate Commission Earnings Report
require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/functions/mlm_commission_ledger.php';
require_once __DIR__ . '/../includes/functions/notification_util.php';
require_once __DIR__ . '/../includes/functions/permission.php'; // Assuming permission.php contains the require_permission function

session_start();
// TODO: Add authentication and restrict to associate or admin

require_permission('view_associate_commission_report');

$aid = isset($_GET['aid']) ? intval($_GET['aid']) : 0;
if ($aid <= 0 && isset($_SESSION['uid'])) {
    $aid = intval($_SESSION['uid']);
}

$total_commission = getTotalCommissionEarned($con, $aid);
$ledger = getCommissionLedger($con, $aid);

// After associate commission report generation or approval
addNotification($con, 'Commission', 'Associate commission report generated or approved.', $_SESSION['auser'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Commission Earnings Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>Commission Earnings Report</h2>
        <div class="mb-3"><strong>Total Commission Earned:</strong> ₹<?php echo number_format($total_commission, 2); ?></div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Level</th>
                    <th>Commission Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ledger->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['level']); ?></td>
                        <td>₹<?php echo number_format($row['commission_amount'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
