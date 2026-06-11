<?php $pageTitle = 'Associate Metrics'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate">users</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/show/<?= $associate['id'] ?? '' ?>"><?= htmlspecialchars($associate['name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Metrics</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance Metrics: <?= htmlspecialchars($associate['name'] ?? '') ?></h4>
    </div>
    <?php if (!empty($associate)): ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-building"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['total_properties'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Properties Listed</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['properties_sold'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Properties Sold</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($metrics['total_revenue'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-trophy"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['conversion_rate'] ?? 0 ?>%</h3>
                    <p class="text-muted mb-0">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Performance</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($monthlyData)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Month</th><th>Properties</th><th>Sales</th><th>Revenue</th><th>Commission</th></tr></thead>
                            <tbody>
                                <?php foreach ($monthlyData as $m): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($m['month'] ?? '') ?></td>
                                    <td><?= $m['properties'] ?? 0 ?></td>
                                    <td><?= $m['sales'] ?? 0 ?></td>
                                    <td>₹<?= number_format($m['revenue'] ?? 0) ?></td>
                                    <td>₹<?= number_format($m['commission'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-bar fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No monthly data available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-star me-2"></i>Summary</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($metrics)): ?>
                        <div class="mb-2"><small class="text-muted">Avg. Deal Size</small><br><strong>₹<?= number_format($metrics['avg_deal_size'] ?? 0) ?></strong></div>
                        <div class="mb-2"><small class="text-muted">Total Commission Earned</small><br><strong>₹<?= number_format($metrics['total_commission'] ?? 0) ?></strong></div>
                        <div class="mb-2"><small class="text-muted">Leads Generated</small><br><strong><?= $metrics['leads_generated'] ?? 0 ?></strong></div>
                        <div class="mb-0"><small class="text-muted">Active Months</small><br><strong><?= $metrics['active_months'] ?? 0 ?></strong></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-simple fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No metrics available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted">Associate Not Found</h5>
            <a href="<?= BASE_URL ?>/associate" class="btn btn-primary mt-2">Back to users</a>
        </div>
    </div>
    <?php endif; ?>
</div>
