<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Marketing Analytics') ?></h1>
        <div class="btn-group">
            <select id="periodSelect" class="form-select form-select-sm" onchange="changePeriod(this.value)" class="style-68062">
                <option value="7">Last 7 Days</option>
                <option value="30" selected>Last 30 Days</option>
                <option value="90">Last 90 Days</option>
                <option value="365">Last Year</option>
            </select>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?= (int)($analytics['impressions'] ?? 0) ?></h3>
                    <small class="text-muted">Impressions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-mouse-pointer fa-2x text-success mb-2"></i>
                    <h3 class="mb-0"><?= (int)($analytics['clicks'] ?? 0) ?></h3>
                    <small class="text-muted">Clicks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?= round((float)($analytics['ctr'] ?? 0), 2) ?>%</h3>
                    <small class="text-muted">CTR</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?= (int)($analytics['leads'] ?? 0) ?></h3>
                    <small class="text-muted">Leads Generated</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Campaign Performance</h5></div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="performanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Traffic Sources</h5></div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="sourceChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Campaign Breakdown</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                            <thead class="table-light">
                                <tr><th>Campaign</th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Leads</th><th>Cost/Lead</th><th>ROI</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($analytics['campaigns'] ?? [])): ?>
                                    <?php foreach ($analytics['campaigns'] as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
                                            <td><?= (int)($c['impressions'] ?? 0) ?></td>
                                            <td><?= (int)($c['clicks'] ?? 0) ?></td>
                                            <td><?= round((float)($c['ctr'] ?? 0), 2) ?>%</td>
                                            <td><?= (int)($c['leads'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars($c['cost_per_lead'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= ((float)($c['roi'] ?? 0)) >= 0 ? 'success' : 'danger' ?>">
                                                    <?= round((float)($c['roi'] ?? 0), 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">No campaign data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
<?php if (!empty($analytics['campaigns'] ?? [])): ?>
new Chart(document.getElementById('performanceChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($c) => $c['name'] ?? '', $analytics['campaigns'])) ?>,
        datasets: [
            { label: 'Impressions', data: <?= json_encode(array_map(fn($c) => (int)($c['impressions'] ?? 0), $analytics['campaigns'])) ?>, backgroundColor: '#0d6efd' },
            { label: 'Clicks', data: <?= json_encode(array_map(fn($c) => (int)($c['clicks'] ?? 0), $analytics['campaigns'])) ?>, backgroundColor: '#198754' }
        ]
    }
});
<?php endif; ?>
<?php if (!empty($analytics['sources'] ?? [])): ?>
new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($analytics['sources'])) ?>,
        datasets: [{ data: <?= json_encode(array_values($analytics['sources'])) ?>, backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d'] }]
    }
});
<?php endif; ?>
function changePeriod(days) {
    window.location.href = '<?= $base ?? BASE_URL ?>/marketing/analytics?days=' + days;
}
</script>