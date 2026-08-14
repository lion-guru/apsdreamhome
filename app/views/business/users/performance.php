<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Performance Metrics</h4>
        <a href="<?= BASE_URL ?>/business/users" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Total Sales</h6>
                            <h3 class="mb-0 mt-1">â‚¹<?= number_format($metrics['total_sales'] ?? 0) ?></h3>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Conversion Rate</h6>
                            <h3 class="mb-0 mt-1"><?= number_format($metrics['conversion_rate'] ?? 0, 1) ?>%</h3>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Avg Deal Size</h6>
                            <h3 class="mb-0 mt-1">â‚¹<?= number_format($metrics['avg_deal_size'] ?? 0) ?></h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Team Size</h6>
                            <h3 class="mb-0 mt-1"><?= number_format($metrics['team_size'] ?? 0) ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="mb-0">Monthly Performance</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="style-27886">
                        <canvas id="performanceChart" class="style-40817"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="mb-0">Quick Summary</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <small class="text-muted">Best Month</small>
                        <p class="mb-0 fw-bold"><?= htmlspecialchars($metrics['best_month'] ?? 'â€”') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Leads Generated</small>
                        <p class="mb-0 fw-bold"><?= number_format($metrics['total_leads'] ?? 0) ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Deals Closed (All Time)</small>
                        <p class="mb-0 fw-bold"><?= number_format($metrics['deals_closed'] ?? 0) ?></p>
                    </div>
                    <div>
                        <small class="text-muted">Total Commission Earned</small>
                        <p class="mb-0 fw-bold">â‚¹<?= number_format($metrics['total_commission'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0">Monthly Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Leads Generated</th>
                            <th>Deals Closed</th>
                            <th>Revenue</th>
                            <th>Commission Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($performance ?? [])): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No performance data available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($performance as $row): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['month'] ?? '') ?></strong></td>
                                    <td><?= number_format($row['leads_generated'] ?? 0) ?></td>
                                    <td><?= number_format($row['deals_closed'] ?? 0) ?></td>
                                    <td>â‚¹<?= number_format($row['revenue'] ?? 0) ?></td>
                                    <td>â‚¹<?= number_format($row['commission_earned'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('performanceChart');
    if (ctx) {
        ctx = ctx.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($performance ?? [], 'month')) ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?= json_encode(array_map(fn($r) => $r['revenue'] ?? 0, $performance ?? [])) ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }, {
                    label: 'Commission',
                    data: <?= json_encode(array_map(fn($r) => $r['commission_earned'] ?? 0, $performance ?? [])) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.6)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>
