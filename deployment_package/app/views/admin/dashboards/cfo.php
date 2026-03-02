<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Dashboard (CFO)</h2>
        </div>
    </div>

    <!-- Revenue & Payout Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Monthly Revenue</h6>
                    <h3>₹<?php echo $revenue_report['monthly'] ?? '0'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>8% from last month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Pending Payouts</h6>
                    <h3>₹<?php echo $pending_payouts['amount'] ?? '0'; ?></h3>
                    <p class="text-muted mb-0"><?php echo $pending_payouts['count'] ?? '0'; ?> agents waiting</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Collections</h6>
                    <h3>₹<?php echo $revenue_report['total'] ?? '0'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>92% Target Achieved</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Revenue Trends</h5>
                </div>
                <div class="card-body" style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <p>Financial Visualization (Chart.js Integration Pending)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Payouts</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center">
                                    <td colspan="3" class="py-4 text-muted small">No recent transactions found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
