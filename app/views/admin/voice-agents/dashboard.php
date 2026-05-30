<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-phone-voice me-2"></i> Voice Agents Dashboard</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents/schedule" class="btn btn-outline-primary me-2">
                <i class="fas fa-calendar-alt"></i> View Schedule
            </a>
            <a href="<?= BASE_URL ?>/admin/voice-agents/extracted-leads" class="btn btn-outline-success">
                <i class="fas fa-database"></i> Extracted Leads
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 small mb-1">Today's Calls</h6>
                            <h2 class="mb-0 fw-bold"><?= $today_calls ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-phone-alt fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 small mb-1">Connected</h6>
                            <h2 class="mb-0 fw-bold"><?= $connected ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-dark-50 small mb-1">Interested Leads</h6>
                            <h2 class="mb-0 fw-bold"><?= $interested ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-thumbs-up fa-2x text-dark-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 small mb-1">Bookings / Conversion</h6>
                            <h2 class="mb-0 fw-bold"><?= $bookings ?? 0 ?> <small class="fs-6">/ <?= $conversion_rate ?? 0 ?>%</small></h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Call Trend (7 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="callTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Lead Sources</h6>
                </div>
                <div class="card-body">
                    <canvas id="leadSourceChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Performance + Recent Calls -->
    <div class="row mb-4">
        <div class="col-md-5 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Agent Performance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Agent</th>
                                    <th class="small text-center">Calls</th>
                                    <th class="small text-center">Success</th>
                                    <th class="small text-center">Avg Dur</th>
                                    <th class="small text-center">Conv%</th>
                                    <th class="small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agents)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No agent data available</td></tr>
                                <?php else: ?>
                                <?php foreach ($agents as $agent): ?>
                                <tr>
                                    <td class="fw-small"><?= htmlspecialchars($agent['agent_name'] ?? '-') ?></td>
                                    <td class="text-center"><?= (int)($agent['total_calls_made'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($agent['successful_calls'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($agent['avg_call_duration'] ?? 0) ?>s</td>
                                    <td class="text-center"><?= $agent['conversion_rate'] ?? 0 ?>%</td>
                                    <td>
                                        <span class="badge bg-<?= ($agent['status'] ?? '') === 'active' ? 'success' : (($agent['status'] ?? '') === 'busy' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($agent['status'] ?? 'offline') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Recent Calls</h6>
                    <a href="<?= BASE_URL ?>/admin/voice-agents/history" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Lead</th>
                                    <th class="small">Phone</th>
                                    <th class="small">Agent</th>
                                    <th class="small text-center">Status</th>
                                    <th class="small text-center">Duration</th>
                                    <th class="small">Outcome</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_calls)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No recent calls</td></tr>
                                <?php else: ?>
                                <?php foreach ($recent_calls as $call): ?>
                                <tr>
                                    <td class="fw-medium"><?= htmlspecialchars($call['lead_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($call['phone'] ?? $call['lead_phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($call['agent_display_name'] ?? $call['ai_agent_id'] ?? 'Auto') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $call['status'] === 'completed' ? 'success' : ($call['status'] === 'in_progress' ? 'info' : ($call['status'] === 'failed' ? 'danger' : 'warning')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $call['status'] ?? 'unknown')) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $call['duration_seconds'] ? gmdate('i:s', $call['duration_seconds']) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($call['customer_response'])): ?>
                                        <span class="badge bg-<?= $call['customer_response'] === 'interested' ? 'success' : ($call['customer_response'] === 'not_interested' ? 'danger' : 'secondary') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $call['customer_response'])) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Quick Actions</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>/admin/voice-agents/scripts" class="btn btn-primary">
                            <i class="fas fa-scroll me-1"></i> Manage Scripts
                        </a>
                        <a href="<?= BASE_URL ?>/admin/voice-agents/schedule" class="btn btn-warning">
                            <i class="fas fa-calendar-alt me-1"></i> Schedule Calls
                        </a>
                        <a href="<?= BASE_URL ?>/admin/voice-agents/extracted-leads" class="btn btn-success">
                            <i class="fas fa-user-plus me-1"></i> Review Extracted Leads
                        </a>
                        <a href="<?= BASE_URL ?>/admin/voice-agents/oln" class="btn btn-info">
                            <i class="fas fa-seedling me-1"></i> Lead Nurturing Pipeline
                        </a>
                        <a href="<?= BASE_URL ?>/admin/voice-agents/settings" class="btn btn-secondary">
                            <i class="fas fa-cog me-1"></i> Agent Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    var trendCtx = document.getElementById('callTrendChart');
    if (trendCtx) {
        var trendLabels = <?= json_encode(array_map(function($r) { return $r['date'] ?? ''; }, $call_trend ?? [])) ?>;
        var trendTotal = <?= json_encode(array_map(function($r) { return (int)($r['total'] ?? 0); }, $call_trend ?? [])) ?>;
        var trendCompleted = <?= json_encode(array_map(function($r) { return (int)($r['completed'] ?? 0); }, $call_trend ?? [])) ?>;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Calls',
                    data: trendTotal,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Completed',
                    data: trendCompleted,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    var sourceCtx = document.getElementById('leadSourceChart');
    if (sourceCtx) {
        var sourceLabels = <?= json_encode(array_map(function($r) { return ucfirst($r['interest_level'] ?? 'unknown'); }, $lead_sources ?? [])) ?>;
        var sourceData = <?= json_encode(array_map(function($r) { return (int)($r['count'] ?? 0); }, $lead_sources ?? [])) ?>;
        var colors = { Hot: '#ef4444', Warm: '#f59e0b', Cold: '#3b82f6', None: '#9ca3af' };
        var bgColors = sourceLabels.map(function(l) { return colors[l] || '#6b7280'; });

        if (sourceLabels.length === 0) {
            sourceLabels = ['No Data'];
            sourceData = [1];
            bgColors = ['#e5e7eb'];
        }

        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: sourceLabels,
                datasets: [{ data: sourceData, backgroundColor: bgColors }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
