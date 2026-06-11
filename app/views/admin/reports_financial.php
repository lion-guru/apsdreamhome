<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Financial Reports'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-invoice-dollar me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Financial Reports')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Financial</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $finData = $financial_data ?? []; ?>
    <?php if (!empty($finData)): ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-2x text-success mb-2"></i>
                    <h5>₹<?= number_format($profit_loss['total_revenue'] ?? $finData['total_revenue'] ?? 0) ?></h5>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-2x text-danger mb-2"></i>
                    <h5>₹<?= number_format($profit_loss['total_expenses'] ?? $finData['total_expenses'] ?? 0) ?></h5>
                    <small class="text-muted">Total Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                    <h5>₹<?= number_format($profit_loss['net_profit'] ?? $finData['net_profit'] ?? 0) ?></h5>
                    <small class="text-muted">Net Profit</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>Revenue</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Month</th><th>Revenue</th><th>Commission</th></tr></thead>
                            <tbody>
                                <?php foreach (($revenue_data ?? $finData['revenue_data'] ?? []) as $rev): ?>
                                <tr>
                                    <td><?= ($rev['month'] ?? '') ?></td>
                                    <td>₹<?= number_format($rev['revenue'] ?? 0) ?></td>
                                    <td>₹<?= number_format($rev['commission'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($revenue_data ?? $finData['revenue_data'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No revenue data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Commission</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Agent</th><th>Properties Sold</th><th>Commission</th></tr></thead>
                            <tbody>
                                <?php foreach (($commission_data ?? $finData['commission_data'] ?? []) as $comm): ?>
                                <tr>
                                    <td><?= ($comm['agent'] ?? '') ?></td>
                                    <td><?= ($comm['properties_sold'] ?? 0) ?></td>
                                    <td>₹<?= number_format($comm['commission'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($commission_data ?? $finData['commission_data'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No commission data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Expenses</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Category</th><th>Amount</th></tr></thead>
                            <tbody>
                                <?php foreach (($expense_data ?? $finData['expense_data'] ?? []) as $exp): ?>
                                <tr>
                                    <td><?= ($exp['category'] ?? '') ?></td>
                                    <td>₹<?= number_format($exp['amount'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($expense_data ?? $finData['expense_data'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No expense data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-percentage me-2"></i>Profit & Loss Summary</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $pl = $profit_loss ?? $finData['profit_loss'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Revenue</span><strong>₹<?= number_format($pl['total_revenue'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Expenses</span><strong class="text-danger">-₹<?= number_format($pl['total_expenses'] ?? 0) ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Net Profit</span><strong class="text-success">₹<?= number_format($pl['net_profit'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Margin</span><strong><?= ($pl['profit_margin'] ?? 0) ?>%</strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-invoice-dollar fa-4x text-muted mb-3"></i>
            <h5>No Financial Report Data</h5>
            <p class="text-muted mb-0">Financial reports will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
