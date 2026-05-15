<?php
$page_title = $page_title ?? 'My Commissions - APS Dream Home';
$commissions = $commissions ?? [];
$total_earned = $total_earned ?? 0;
$total_pending = $total_pending ?? 0;
?>
<div class="container-fluid px-4">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6>Total Earned</h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_earned); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3 class="mb-0">₹<?php echo number_format($total_pending); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>Total Transactions</h6>
                    <h3 class="mb-0"><?php echo count($commissions); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i>Commission History</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($commissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No commission transactions yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['property'] ?? 'N/A'); ?></td>
                                    <td><strong>₹<?php echo number_format($c['amount'] ?? 0); ?></strong></td>
                                    <td><span class="badge bg-<?php echo ($c['status'] ?? '') === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($c['status'] ?? 'Pending'); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['date'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
