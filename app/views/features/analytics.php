<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Feature Analytics') ?></h1>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Usage by Feature</h5></div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="usageChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Status Distribution</h5></div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Detailed Analytics</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                            <thead class="table-light">
                                <tr>
                                    <th>Feature</th>
                                    <th>Total Uses</th>
                                    <th>Unique Users</th>
                                    <th>Avg Duration</th>
                                    <th>Success Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($analytics)): ?>
                                    <?php foreach ($analytics as $a): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($a['feature'] ?? '') ?></td>
                                            <td><?= (int)($a['total_uses'] ?? 0) ?></td>
                                            <td><?= (int)($a['unique_users'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars($a['avg_duration'] ?? '-') ?></td>
                                            <td>
                                                <div class="progress" style="height:8px;max-width:120px">
                                                    <div class="progress-bar bg-success" style="width:<?= min(100, (float)($a['success_rate'] ?? 0)) ?>%"></div>
                                                </div>
                                                <small><?= round((float)($a['success_rate'] ?? 0), 1) ?>%</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= (($a['trend'] ?? 'stable') === 'up') ? 'success' : (($a['trend'] ?? 'stable') === 'down' ? 'danger' : 'secondary') ?>">
                                                    <i class="fas fa-arrow-<?= ($a['trend'] ?? 'stable') === 'up' ? 'up' : (($a['trend'] ?? 'stable') === 'down' ? 'down' : 'right') ?>"></i>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No analytics data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const usageCtx = document.getElementById('usageChart')?.getContext('2d');
const statusCtx = document.getElementById('statusChart')?.getContext('2d');
<?php if (!empty($analytics)): ?>
new Chart(usageCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($a) => $a['feature'] ?? '', $analytics)) ?>,
        datasets: [{ label: 'Uses', data: <?= json_encode(array_map(fn($a) => (int)($a['total_uses'] ?? 0), $analytics)) ?>, backgroundColor: '#0d6efd' }]
    }
});
<?php endif; ?>
<?php if (!empty($analytics)): ?>
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Inactive', 'Beta', 'Deprecated'],
        datasets: [{
            data: [
                <?= count(array_filter($analytics, fn($a) => ($a['status'] ?? '') === 'active')) ?>,
                <?= count(array_filter($analytics, fn($a) => ($a['status'] ?? '') === 'inactive')) ?>,
                <?= count(array_filter($analytics, fn($a) => ($a['status'] ?? '') === 'beta')) ?>,
                <?= count(array_filter($analytics, fn($a) => ($a['status'] ?? '') === 'deprecated')) ?>
            ],
            backgroundColor: ['#198754', '#6c757d', '#ffc107', '#dc3545']
        }]
    }
});
<?php endif; ?>
</script>