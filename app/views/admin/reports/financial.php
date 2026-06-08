<?php $page_title = $page_title ?? 'Financial Reports';
try {
    $db = $this->db ?? null;
    if (!$db) { $config = require dirname(dirname(dirname(dirname(__DIR__)))) . '/config/database.php'; $db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); }
    $totalReceipts = (float)($db->query("SELECT COALESCE(SUM(amount),0) FROM payment_transactions WHERE payment_status = 'completed'")->fetchColumn());
    $totalBookings = (float)($db->query("SELECT COALESCE(SUM(booking_amount),0) FROM plot_bookings WHERE status != 'cancelled'")->fetchColumn());
    $gstOutput = (float)($db->query("SELECT COALESCE(SUM(total_tax),0) FROM gst_transactions WHERE transaction_type = 'output'")->fetchColumn());
    $gstInput = (float)($db->query("SELECT COALESCE(SUM(total_tax),0) FROM gst_transactions WHERE transaction_type = 'input'")->fetchColumn());
    $gstPayable = max(0, $gstOutput - $gstInput);
    $totalTds = (float)($db->query("SELECT COALESCE(SUM(total_tds),0) FROM tds_register")->fetchColumn());
    $depositedTds = (float)($db->query("SELECT COALESCE(SUM(total_tds),0) FROM tds_register WHERE status IN ('deposited','verified')")->fetchColumn());
    $pendingTds = $totalTds - $depositedTds;
    $bankAccounts = $db->query("SELECT * FROM bank_accounts_master WHERE active = 1")->fetchAll(PDO::FETCH_ASSOC);
    $totalBankBalance = array_sum(array_map(function($a) { return (float)$a['current_balance']; }, $bankAccounts));
    $escrowBalance = array_sum(array_map(function($a) { return $a['is_escrow'] ? (float)$a['current_balance'] : 0; }, $bankAccounts));
    $reconciliations = (int)($db->query("SELECT COUNT(*) FROM bank_reconciliation WHERE status = 'completed'")->fetchColumn());
    $pendingRecon = (int)($db->query("SELECT COUNT(*) FROM bank_reconciliation WHERE status != 'completed'")->fetchColumn());
    $monthlyData = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue, payment_method FROM payment_transactions WHERE payment_status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month, payment_method ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
    $methodBreakdown = $db->query("SELECT payment_method, COUNT(*) as cnt, SUM(amount) as total FROM payment_transactions WHERE payment_status = 'completed' GROUP BY payment_method ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
    $recentPayments = $db->query("SELECT pt.*, u.name FROM payment_transactions pt LEFT JOIN users u ON pt.user_id = u.id ORDER BY pt.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $totalReceipts = $totalBookings = $gstOutput = $gstInput = $gstPayable = $totalTds = $depositedTds = $pendingTds = $totalBankBalance = $escrowBalance = 0; $bankAccounts = $monthlyData = $methodBreakdown = $recentPayments = []; $reconciliations = $pendingRecon = 0; }
$netIncome = $totalReceipts;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Financial Reports</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-primary btn-sm"><i class="fas fa-book me-1"></i>Cash Book</a>
            <a href="<?= BASE_URL ?>/admin/finance/tds" class="btn btn-outline-primary btn-sm"><i class="fas fa-percentage me-1"></i>TDS</a>
            <a href="<?= BASE_URL ?>/admin/finance/gst" class="btn btn-outline-primary btn-sm"><i class="fas fa-receipt me-1"></i>GST</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-arrow-up"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Income</div><div class="aps-cp-stat-value text-success">₹<?= number_format($totalReceipts/100000,1) ?>L</div><div class="aps-cp-stat-meta">Bookings: ₹<?= number_format($totalBookings/100000,1) ?>L</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-receipt"></i></span></div>
                    <div><div class="aps-cp-stat-label">GST Payable</div><div class="aps-cp-stat-value">₹<?= number_format($gstPayable) ?></div><div class="aps-cp-stat-meta">Output: ₹<?= number_format($gstOutput) ?> | ITC: ₹<?= number_format($gstInput) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-percentage"></i></span></div>
                    <div><div class="aps-cp-stat-label">TDS Deducted</div><div class="aps-cp-stat-value">₹<?= number_format($totalTds) ?></div><div class="aps-cp-stat-meta">Deposited: ₹<?= number_format($depositedTds) ?> | Pending: ₹<?= number_format($pendingTds) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-university"></i></span></div>
                    <div><div class="aps-cp-stat-label">Bank Balance</div><div class="aps-cp-stat-value">₹<?= number_format($totalBankBalance/100000,1) ?>L</div><div class="aps-cp-stat-meta">Escrow: ₹<?= number_format($escrowBalance/100000,1) ?>L</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-university me-2"></i>Bank Accounts</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($bankAccounts)): ?>
                        <div class="text-center text-muted py-3">No bank accounts</div>
                    <?php else: ?>
                        <?php foreach ($bankAccounts as $ba): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div><strong class="small"><?= htmlspecialchars($ba['account_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($ba['bank_name']) ?></small></div>
                                <div class="text-end"><strong class="text-success">₹<?= number_format($ba['current_balance']) ?></strong><br><small class="text-muted"><?= $ba['is_escrow'] ? 'Escrow' : ucfirst($ba['account_type']) ?></small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-credit-card me-2"></i>Payment Methods</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($methodBreakdown)): ?>
                        <div class="text-center text-muted py-3">No payments recorded</div>
                    <?php else: ?>
                        <?php foreach ($methodBreakdown as $m): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-capitalize"><i class="fas fa-credit-card me-1"></i><?= htmlspecialchars($m['payment_method']) ?></span>
                                <div class="text-end"><strong>₹<?= number_format($m['total']) ?></strong><br><small class="text-muted"><?= $m['cnt'] ?> txns</small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-balance-scale me-2"></i>Bank Reconciliation</div>
                <div class="aps-cp-card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Completed</small><small class="text-success"><?= $reconciliations ?></small></div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?= ($reconciliations + $pendingRecon) > 0 ? round($reconciliations/($reconciliations+$pendingRecon)*100) : 0 ?>%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Pending</small><small class="text-warning"><?= $pendingRecon ?></small></div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-warning" style="width:<?= ($reconciliations + $pendingRecon) > 0 ? round($pendingRecon/($reconciliations+$pendingRecon)*100) : 0 ?>%"></div></div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/admin/finance/reconciliation" class="btn btn-outline-primary btn-sm">View Reconciliation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Recent Payments</div>
        <div class="aps-cp-card-body">
            <?php if (empty($recentPayments)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-credit-card fa-2x mb-2"></i><p>No payments recorded</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentPayments as $p): ?>
                            <tr>
                                <td>#<?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['name'] ?? 'N/A') ?></td>
                                <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
                                <td><span class="text-capitalize"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                                <td><span class="aps-cp-badge badge bg-<?= $p['payment_status'] === 'completed' ? 'success' : ($p['payment_status'] === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst(htmlspecialchars($p['payment_status'])) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($p['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
