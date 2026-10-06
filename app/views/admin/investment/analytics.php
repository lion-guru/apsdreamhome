<?php
$page_title = $page_title ?? 'Investment Analytics Dashboard';
$base = defined('BASE_URL') ? BASE_URL : '';
$stats = $stats ?? [];
$planPerformance = $planPerformance ?? [];
$recentActivity = $recentActivity ?? [];
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-chart-line me-2 text-primary"></i>Investment Analytics Dashboard</h4>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/investment/plan-performance" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-bar me-1"></i>Plan Performance</a>
            <a href="<?= $base ?>/admin/investment/installment-report" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-invoice me-1"></i>Installment Report</a>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <?php if (!empty($stats['overview'])): ?>
        <?php $o = $stats['overview']; ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value fw-bold fs-3"><?= number_format($o['total_investments'] ?? 0) ?></div>
                                <div class="stat-label small opacity-75">Total Investments</div>
                            </div>
                            <div class="stat-icon fs-2 opacity-50"><i class="fas fa-piggy-bank"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value fw-bold fs-3">₹<?= number_format((float)($o['total_principal'] ?? 0)) ?></div>
                                <div class="stat-label small opacity-75">Total Principal</div>
                            </div>
                            <div class="stat-icon fs-2 opacity-50"><i class="fas fa-rupee-sign"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value fw-bold fs-3">₹<?= number_format((float)($o['total_current_value'] ?? 0)) ?></div>
                                <div class="stat-label small opacity-75">Current Value</div>
                            </div>
                            <div class="stat-icon fs-2 opacity-50"><i class="fas fa-chart-line"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value fw-bold fs-3"><?= $o['avg_return_pct'] ?? 0 ?>%</div>
                                <div class="stat-label small opacity-75">Avg Return</div>
                            </div>
                            <div class="stat-icon fs-2 opacity-50"><i class="fas fa-percentage"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fw-bold fs-4 text-primary"><?= number_format($o['active_investments'] ?? 0) ?></div>
                        <small class="text-muted">Active Investments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fw-bold fs-4 text-success"><?= number_format($o['matured_investments'] ?? 0) ?></div>
                        <small class="text-muted">Matured</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fw-bold fs-4 text-danger"><?= number_format($o['cancelled_investments'] ?? 0) ?></div>
                        <small class="text-muted">Cancelled</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fw-bold fs-4 text-info">₹<?= number_format((float)($o['total_returns'] ?? 0)) ?></div>
                        <small class="text-muted">Total Returns</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- SIP Stats -->
    <?php if (!empty($stats['sip_stats'])): ?>
        <?php $sip = $stats['sip_stats']; ?>
        <div class="alert alert-light border mb-4">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="fw-bold fs-5 text-primary"><?= number_format($sip['total_sips'] ?? 0) ?></div>
                    <small class="text-muted">Active SIPs</small>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold fs-5 text-success">₹<?= number_format((float)($sip['total_monthly_commitment'] ?? 0)) ?></div>
                    <small class="text-muted">Monthly Commitment</small>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold fs-5 text-warning">₹<?= number_format((float)($sip['total_sip_principal'] ?? 0)) ?></div>
                    <small class="text-muted">SIP Principal</small>
                </div>
                <div class="col-md-3">
                    <?php if (!empty($stats['installment_stats'])): ?>
                        <?php $paid = 0; $failed = 0; foreach ($stats['installment_stats'] as $inst) { if ($inst['status'] === 'paid') $paid += (int)$inst['count']; if ($inst['status'] === 'failed') $failed += (int)$inst['count']; } ?>
                        <div class="fw-bold fs-5 text-success"><?= $paid ?></div>
                        <small class="text-muted">Installments Paid</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-chart-area me-2"></i>Monthly Investment Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-chart-pie me-2"></i>Investments by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-shield-alt me-2"></i>Investments by Risk Level</h5>
                </div>
                <div class="card-body">
                    <canvas id="riskChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-users me-2"></i>Top 10 Investors</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($stats['top_investors'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Investor</th>
                                        <th class="text-end">Invested</th>
                                        <th class="text-end">Returns</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($stats['top_investors'], 0, 10) as $inv): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                                        <?= strtoupper(substr($inv['name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium small"><?= htmlspecialchars($inv['name'] ?? 'Unknown') ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($inv['email'] ?? '') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">₹<?= number_format((float)($inv['total_invested'] ?? 0)) ?></td>
                                            <td class="text-end text-success">₹<?= number_format((float)($inv['total_returns'] ?? 0)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">No investor data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-history me-2"></i>Recent Investment Activity</h5>
            <a href="<?= $base ?>/admin/investments" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($recentActivity)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref</th>
                                <th>Investor</th>
                                <th>Plan</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $act): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($act['investment_ref'] ?? '') ?></code></td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($act['user_name'] ?? 'Unknown') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($act['email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($act['plan_name'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $act['plan_category'] === 'sip' ? 'primary' : ($act['plan_category'] === 'lumpsum' ? 'success' : ($act['plan_category'] === 'real_estate_fund' ? 'warning' : 'info')) ?>">
                                            <?= ucfirst($act['plan_category'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-medium">₹<?= number_format((float)($act['principal_amount'] ?? 0)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $act['status'] === 'active' ? 'success' : 
                                            ($act['status'] === 'matured' ? 'info' : 
                                            ($act['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                            <?= ucfirst($act['status'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d M Y', strtotime($act['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No recent investment activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card { border-radius: 15px; transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.stat-value { font-size: 1.5rem; }
.stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-icon { font-size: 2.5rem; }
.avatar-sm { width: 36px; height: 36px; font-size: 0.85rem; }
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyData = <?= json_encode($stats['monthly_trends'] ?? []) ?>;
    const months = monthlyData.map(d => d.month);
    const counts = monthlyData.map(d => parseInt(d.count));
    const amounts = monthlyData.map(d => parseFloat(d.total_principal));
    
    new Chart(document.getElementById('monthlyTrendsChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Investments Count',
                data: counts,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Total Principal (₹ Cr)',
                data: amounts.map(a => a / 10000000),
                type: 'line',
                borderColor: '#0d9488',
                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { type: 'linear', position: 'left', beginAtZero: true },
                y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
            },
            plugins: { legend: { position: 'top' } }
        }
    });

    // Category Chart
    const catData = <?= json_encode($stats['by_category'] ?? []) ?>;
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(d => d.plan_category ? d.plan_category.charAt(0).toUpperCase() + d.plan_category.slice(1) : 'Unknown'),
            datasets: [{
                data: catData.map(d => parseFloat(d.total_principal || 0)),
                backgroundColor: ['#667eea', '#0d9488', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // Risk Chart
    const riskData = <?= json_encode($stats['by_risk'] ?? []) ?>;
    new Chart(document.getElementById('riskChart'), {
        type: 'pie',
        data: {
            labels: riskData.map(d => d.risk_level ? d.risk_level.charAt(0).toUpperCase() + d.risk_level.slice(1) : 'Unknown'),
            datasets: [{
                data: riskData.map(d => parseFloat(d.total_principal || 0)),
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>