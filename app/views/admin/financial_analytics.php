<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Financial Analytics'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-line me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Financial Analytics')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Financial Analytics</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $finData = $financial_stats ?? $financial_data ?? []; ?>
    <?php if (!empty($finData)): ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>Revenue Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Month</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach (($finData['revenue_trends'] ?? []) as $row): ?>
                                <tr>
                                    <td><?= ($row['month'] ?? '') ?></td>
                                    <td>₹<?= number_format($row['revenue'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($finData['revenue_trends'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No revenue data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-coins me-2"></i>Commission Analysis</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $comm = $finData['commission_analysis'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Total Commission Paid</span><strong>₹<?= number_format($comm['total_commission_paid'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Commission/Sale</span><strong>₹<?= number_format($comm['avg_commission_per_sale'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Top Earning Associate</span><strong><?= $comm['top_earning_associate'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Commission Growth</span><strong><?= $comm['commission_growth_rate'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Methods</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Method</th><th>Count</th><th>Amount</th></tr></thead>
                            <tbody>
                                <?php foreach (($finData['payment_methods'] ?? []) as $pm): ?>
                                <tr>
                                    <td><?= ($pm['method'] ?? '') ?></td>
                                    <td><?= ($pm['count'] ?? 0) ?></td>
                                    <td>₹<?= number_format($pm['amount'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($finData['payment_methods'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No payment data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-percentage me-2"></i>Profit Margins</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $margins = $finData['profit_margins'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Margin</span><strong><?= $margins['avg_margin'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Highest Margin</span><strong><?= $margins['highest_margin'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Lowest Margin</span><strong><?= $margins['lowest_margin'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Margin Trend</span><strong><?= $margins['margin_trend'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h5>No Financial Data</h5>
            <p class="text-muted mb-0">Financial analytics will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
