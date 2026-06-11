<?php $stats = $stats ?? []; $agents = $agents ?? []; $callsOverTime = $callsOverTime ?? []; $pendingCalls = $pendingCalls ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Voice Call Scheduler</h4>
    <div>
        <a href="<?= BASE_URL ?>admin/voice-scheduler/schedule" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Schedule Call</a>
        <a href="<?= BASE_URL ?>admin/voice-scheduler/analytics" class="btn btn-info btn-sm text-white"><i class="fas fa-chart-bar me-1"></i>Analytics</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['today_scheduled'] ?? 0) ?></h3>
                <small>Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['total_pending'] ?? 0) ?></h3>
                <small>Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['total_completed'] ?? 0) ?></h3>
                <small>Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0"><?= (int)($stats['total_failed'] ?? 0) ?></h3>
                <small>Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-12">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i>Pending Calls / Queue</span>
                <form method="post" action="<?= BASE_URL ?>admin/voice-scheduler/process" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-play me-1"></i>Process Queue</button>
                </form>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingCalls)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-check-circle me-2"></i>No pending calls in queue</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr>
                            <th>Lead</th><th>Phone</th><th>Scheduled</th><th>Agent</th><th>Priority</th><th>Action</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($pendingCalls as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['lead_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? $c['lead_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= date('d M Y', strtotime($c['scheduled_date'] ?? '')) ?><br><small class="text-muted"><?= date('h:i A', strtotime($c['scheduled_time'] ?? '')) ?></small></td>
                                <td><?= htmlspecialchars($c['agent_name'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-<?= $c['priority'] === 'urgent' ? 'danger' : ($c['priority'] === 'high' ? 'warning' : 'secondary') ?>"><?= $c['priority'] ?? 'medium' ?></span></td>
                                <td><a href="<?= BASE_URL ?>admin/voice-scheduler/calls/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header aps-cp-card-header"><i class="fas fa-robot me-2"></i>Agent Status</div>
            <div class="card-body p-0">
                <?php if (empty($agents)): ?>
                <div class="text-center text-muted py-4">No agents configured</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($agents as $a): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-robot me-1 text-<?= $a['status'] === 'active' ? 'success' : ($a['status'] === 'busy' ? 'warning' : 'secondary') ?>"></i>
                            <?= htmlspecialchars($a['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <small class="text-muted"><?= (int)($a['current_calls'] ?? 0) ?>/<?= (int)($a['max_concurrent_calls'] ?? 1) ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header aps-cp-card-header"><i class="fas fa-chart-line me-2"></i>Calls Over Time (14 days)</div>
    <div class="card-body aps-cp-card-body">
        <canvas id="callsChart" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('callsChart');
    if (!ctx) return;
    var labels = <?= json_encode(array_map(function($r) { return $r['date'] ?? ''; }, $callsOverTime)) ?>;
    var total = <?= json_encode(array_map(function($r) { return (int)($r['total'] ?? 0); }, $callsOverTime)) ?>;
    var completed = <?= json_encode(array_map(function($r) { return (int)($r['completed'] ?? 0); }, $callsOverTime)) ?>;
    var failed = <?= json_encode(array_map(function($r) { return (int)($r['failed'] ?? 0); }, $callsOverTime)) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Total', data: total, backgroundColor: 'rgba(13,110,253,0.6)', borderRadius: 4 },
                { label: 'Completed', data: completed, backgroundColor: 'rgba(25,135,84,0.6)', borderRadius: 4 },
                { label: 'Failed', data: failed, backgroundColor: 'rgba(220,53,69,0.6)', borderRadius: 4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });
});
</script>
