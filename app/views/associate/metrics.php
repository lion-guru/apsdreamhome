<?php
$page_title = __('assoc_met_title', [], 'Associate Metrics');
$current_page = 'metrics';
?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i><?= __('assoc_met_home', [], 'Home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/associate/manage/list"><?= __('assoc_met_users', [], 'users') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/associate/manage/show/<?= $associate['id'] ?? '' ?>"><?= htmlspecialchars($associate['name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_met_metrics', [], 'Metrics') ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i><?= __('assoc_met_perf', [], 'Performance Metrics') ?>: <?= htmlspecialchars($associate['name'] ?? '') ?></h4>
    </div>
    <?php if (!empty($associate)): ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-building"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['total_properties'] ?? 0 ?></h3>
                    <p class="text-muted mb-0"><?= __('assoc_met_properties_listed', [], 'Properties Listed') ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['properties_sold'] ?? 0 ?></h3>
                    <p class="text-muted mb-0"><?= __('assoc_met_properties_sold', [], 'Properties Sold') ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($metrics['total_revenue'] ?? 0) ?></h3>
                    <p class="text-muted mb-0"><?= __('assoc_met_total_revenue', [], 'Total Revenue') ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-trophy"></i></div>
                    <h3 class="fw-bold mb-1"><?= $metrics['conversion_rate'] ?? 0 ?>%</h3>
                    <p class="text-muted mb-0"><?= __('assoc_met_conversion', [], 'Conversion Rate') ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= __('assoc_met_monthly', [], 'Monthly Performance') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($monthlyData)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th><?= __('assoc_met_month', [], 'Month') ?></th><th><?= __('assoc_met_properties', [], 'Properties') ?></th><th><?= __('assoc_met_sales', [], 'Sales') ?></th><th><?= __('assoc_met_revenue', [], 'Revenue') ?></th><th><?= __('assoc_met_commission', [], 'Commission') ?></th></tr></thead>
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
                        <div class="text-center py-4"><i class="fas fa-chart-bar fa-2x text-muted mb-2"></i><p class="text-muted mb-0"><?= __('assoc_met_no_monthly', [], 'No monthly data available') ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-star me-2"></i><?= __('assoc_met_summary', [], 'Summary') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($metrics)): ?>
                        <div class="mb-2"><small class="text-muted"><?= __('assoc_met_avg_deal', [], 'Avg. Deal Size') ?></small><br><strong>₹<?= number_format($metrics['avg_deal_size'] ?? 0) ?></strong></div>
                        <div class="mb-2"><small class="text-muted"><?= __('assoc_met_total_comm', [], 'Total Commission Earned') ?></small><br><strong>₹<?= number_format($metrics['total_commission'] ?? 0) ?></strong></div>
                        <div class="mb-2"><small class="text-muted"><?= __('assoc_met_leads_gen', [], 'Leads Generated') ?></small><br><strong><?= $metrics['leads_generated'] ?? 0 ?></strong></div>
                        <div class="mb-0"><small class="text-muted"><?= __('assoc_met_active_months', [], 'Active Months') ?></small><br><strong><?= $metrics['active_months'] ?? 0 ?></strong></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-simple fa-2x text-muted mb-2"></i><p class="text-muted mb-0"><?= __('assoc_met_no_metrics', [], 'No metrics available') ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted"><?= __('assoc_met_not_found', [], 'Associate Not Found') ?></h5>
            <a href="<?= BASE_URL ?>/admin/associate/manage/list" class="btn btn-primary mt-2"><?= __('assoc_met_back_users', [], 'Back to users') ?></a>
        </div>
    </div>
    <?php endif; ?>
</div>
