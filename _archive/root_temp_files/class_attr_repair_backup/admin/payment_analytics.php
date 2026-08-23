<?php $pageTitle = $pageTitle ?? 'Payment Analytics'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Analytics</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-rupee-sign"></i></div>
                    <h5 class="mb-1"><?= number_format($payment_stats['total_revenue'] ?? 0) ?></h5>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-receipt"></i></div>
                    <h5 class="mb-1"><?= number_format($payment_stats['total_transactions'] ?? 0) ?></h5>
                    <small class="text-muted">Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h5 class="mb-1"><?= number_format($payment_stats['successful'] ?? 0) ?></h5>
                    <small class="text-muted">Successful</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                    <h5 class="mb-1"><?= number_format($payment_stats['failed'] ?? 0) ?></h5>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Payment Summary</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Month</th><th>Revenue</th><th>Transactions</th><th>Success Rate</th><th>Avg Value</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payment_stats['monthly'] ?? [])): ?>
                            <?php foreach ($payment_stats['monthly'] as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['month'] ?? '-') ?></td>
                                    <td><?= number_format($m['revenue'] ?? 0) ?></td>
                                    <td><?= number_format($m['transactions'] ?? 0) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" class="style-12222">
                                                <div class="progress-bar bg-success" class="style-18089"></div>
                                            </div>
                                            <small><?= number_format($m['success_rate'] ?? 0, 1) ?>%</small>
                                        </div>
                                    </td>
                                    <td><?= number_format($m['avg_value'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No payment data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
