<?php $stats = $stats ?? []; $callsByAgent = $callsByAgent ?? []; $callsOverTime = $callsOverTime ?? []; $agents = $agents ?? []; $leadSources = $leadSources ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Call Analytics</h4>
    <a href="<?= BASE_URL ?>admin/voice-scheduler" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['today_scheduled'] ?? 0) ?></h3>
                <small>Today Scheduled</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['total_pending'] ?? 0) ?></h3>
                <small>Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['total_completed'] ?? 0) ?></h3>
                <small>Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['agents_active'] ?? 0) ?></h3>
                <small>Active Agents</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Calls Over Time (30 days)</div>
            <div class="card-body aps-cp-card-body">
                <canvas id="callsOverTimeChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header aps-cp-card-header"><i class="fas fa-chart-doughnut me-2"></i>Lead Sources by Interest</div>
            <div class="card-body aps-cp-card-body">
                <canvas id="leadSourceChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header aps-cp-card-header"><i class="fas fa-robot me-2"></i>Agent Performance</div>
    <div class="card-body p-0">
        <?php if (empty($callsByAgent)): ?>
        <div class="text-center text-muted py-4">No call data available</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>Agent</th><th>Status</th><th>Total Calls</th><th>Completed</th><th>Success Rate</th>
                </tr></thead>
                <tbody>
                <?php foreach ($callsByAgent as $a): ?>
                    <tr>
                        <td><i class="fas fa-robot me-1 text-primary"></i><?= htmlspecialchars($a['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-<?= ($a['status'] ?? 'offline') === 'active' ? 'success' : 'secondary' ?>"><?= $a['status'] ?? 'offline' ?></span></td>
                        <td><?= (int)($a['total_calls'] ?? 0) ?></td>
                        <td><?= (int)($a['completed'] ?? 0) ?></td>
                        <td>
                            <?php $rate = (int)($a['total_calls'] ?? 0) > 0 ? round(((int)($a['completed'] ?? 0) / (int)($a['total_calls'] ?? 0)) * 100, 1) : 0; ?>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-success" style="width:<?= $rate ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $rate ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var labels = <?= json_encode(array_map(function($r) { return $r['date'] ?? ''; }, $callsOverTime)) ?>;
    var total = <?= json_encode(array_map(function($r) { return (int)($r['total'] ?? 0); }, $callsOverTime)) ?>;
    var completed = <?= json_encode(array_map(function($r) { return (int)($r['completed'] ?? 0); }, $callsOverTime)) ?>;
    var failed = <?= json_encode(array_map(function($r) { return (int)($r['failed'] ?? 0); }, $callsOverTime)) ?>;
    if (document.getElementById('callsOverTimeChart')) {
        new Chart(document.getElementById('callsOverTimeChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Total', data: total, borderColor: '#0d6efd', tension: 0.3, fill: false },
                    { label: 'Completed', data: completed, borderColor: '#198754', tension: 0.3, fill: false },
                    { label: 'Failed', data: failed, borderColor: '#dc3545', tension: 0.3, fill: false }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });
    }
    var srcLabels = <?= json_encode(array_map(function($r) { return $r['interest_level'] ?? 'unknown'; }, $leadSources)) ?>;
    var srcData = <?= json_encode(array_map(function($r) { return (int)($r['count'] ?? 0); }, $leadSources)) ?>;
    if (document.getElementById('leadSourceChart') && srcLabels.length) {
        new Chart(document.getElementById('leadSourceChart'), {
            type: 'doughnut',
            data: {
                labels: srcLabels,
                datasets: [{ data: srcData, backgroundColor: ['#dc3545','#ffc107','#6c757d'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
