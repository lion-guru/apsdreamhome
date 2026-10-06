<?php
$page_title = $page_title ?? 'Plan Performance';
$base = defined('BASE_URL') ? BASE_URL : '';
$planPerformance = $planPerformance ?? [];
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Plan Performance</h4>
        <a href="<?= $base ?>/admin/investment/analytics" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($planPerformance)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plan</th>
                                <th>Category</th>
                                <th>Expected Return</th>
                                <th>Tenure</th>
                                <th>Total Investments</th>
                                <th class="text-end">Total Principal</th>
                                <th class="text-end">Current Value</th>
                                <th class="text-end">Total Returns</th>
                                <th class="text-end">Avg Return %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($planPerformance as $plan): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($plan['plan_name'] ?? '') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($plan['plan_code'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $plan['plan_category'] === 'sip' ? 'primary' : 
                                            ($plan['plan_category'] === 'lumpsum' ? 'success' : 
                                            ($plan['plan_category'] === 'real_estate_fund' ? 'warning' : 
                                            ($plan['plan_category'] === 'gold' ? 'info' : 'secondary'))) ?>">
                                            <?= ucfirst($plan['plan_category'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($plan['expected_return_pct'] ?? 0) ?>%</td>
                                    <td><?= htmlspecialchars($plan['tenure_months'] ?? 0) ?> months</td>
                                    <td class="fw-medium"><?= number_format($plan['total_investments'] ?? 0) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($plan['total_principal'] ?? 0)) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($plan['total_current_value'] ?? 0)) ?></td>
                                    <td class="text-end text-success fw-medium">₹<?= number_format((float)($plan['total_returns'] ?? 0)) ?></td>
                                    <td class="text-end fw-medium text-<?= ((float)($plan['avg_return_pct'] ?? 0) >= 0) ? 'success' : 'danger' ?>">
                                        <?= number_format((float)($plan['avg_return_pct'] ?? 0), 2) ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No plan performance data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>